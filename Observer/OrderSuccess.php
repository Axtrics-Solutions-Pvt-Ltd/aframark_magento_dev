<?php
namespace Axtrics\Aframark\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class OrderSuccess implements ObserverInterface
{
    protected $_order;
    protected $_customer;
    protected $_curl;
    protected $_configurable;
    protected $helperblock;
     /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Axtrics\Aframark\Model\Aframark $afra,
        \Axtrics\Aframark\Block\Data $helperBlock,
        \Psr\Log\LoggerInterface $logger,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
    ) {
        $this->_order = $order; 
        $this->_countryFactory = $countryFactory;   
        $this->_objectManager = $objectManager;
        $this->_afra = $afra;
        $this->_customer = $customer;
        $this->_curl = $curl;
        $this->helperblock = $helperBlock;
        $this->logger = $logger;
        $this->_configurable = $configurable;

    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
        $orderids = $observer->getEvent()->getOrderIds();
        $app_data=$this->_afra->getCollection()->getFirstItem();

        foreach($orderids as $orderid)
        {
             $orders = $this->_order->load($orderid);
                    $orderItems = $orders->getAllItems();
                    $deta = $orders->getShippingAddress()->getData();
                    if($orders->getCustomerId() === NULL)
                    {
                    $firstname = $orders->getBillingAddress()->getFirstname();
                    $lastname = $orders->getBillingAddress()->getLastname();
                    }
                 else {
    $customer  = $this->_customer->load($orders->getCustomerId());
    $firstname = $customer->getDefaultBillingAddress()->getFirstname();
    $lastname  = $customer->getDefaultBillingAddress()->getLastname();
  $customer_name = $firstname.' '.$lastname;
}
                    $countryCode = $deta['country_id'];
                    $country = $this->_countryFactory->create()->loadByCode($countryCode);
                    $country=$country->getName();
                        $items=array();
                        foreach ($orderItems as $listitems) 
                        {
                        $getproduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($listitems['product_id']);
                           $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
                                $producturl=$getproduct->getProductUrl();
                            $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $getproduct->getImage();
                                 $parentIds = $this->_configurable->getParentIdsByChild($listitems['product_id']);
                         $parentId = array_shift($parentIds);
                         
                         if($parentId){
                         $parentrepository=$this->_objectManager->create('Magento\Catalog\Model\Product')->load($parentId);
                         $parentsku="";
                         $parentsku=$parentrepository->getSku();
                         }
                         else
                         {
                             $parentsku="null";
                         }
                            if ($app_data['upc_attribute_code']!=null) {
                            $upc=$app_data['upc_attribute_code'];
                        }
                        else
                        {
                            $upc="Null";
                        }
                        if ($app_data['ean_attribute_code']!=null) {
                            $ean=$app_data['ean_attribute_code'];
                        }
                        else
                        {
                            $ean="Null";
                        }
                        if ($app_data['mpn_attribute_code']!=null) {
                            $mpn=$app_data['mpn_attribute_code'];
                        }
                        else
                        {
                            $mpn="Null";
                        }
                        if ($app_data['isbn_attribute_code']!=null) {
                            $isbn=$app_data['isbn_attribute_code'];
                        }
                        else
                        {
                            $isbn="Null";
                        }
                            $items[]=array('id'=>$listitems['item_id'],'title'=>$getproduct['name'],'image'=>$productImageUrl,'parent_sku'=>$parentsku!="null"?$parentsku:$listitems['sku'],'sku'=>$listitems['sku'],'upc'=>$getproduct[$upc],'ean'=>$getproduct[$ean],'mpn'=>$getproduct[$mpn],'isbn'=>$getproduct[$isbn],'url'=>$producturl);
                        }
        $dataa[]=array('id' => $orders->getIncrementId(),'created_at'=>$orders['created_at'],'customer'=>array('email'=>$orders['customer_email'],'first_name'=>$firstname,'last_name'=>$lastname,'country'=>$country),'line_items' =>$items);
                    }
                       
                        $responsedata=array( 'status' => 200,'action'=>'NewOrder','merchant_code'=>$app_data['merchant_code'],
                    'orders' => $dataa);
                      
        $url=$this->helperblock->getAfraUrl();
        
        $this->_curl->post($url, $responsedata);
        
        $response = $this->_curl->getBody();
        
        }
         catch(\Exception $e){

$this->logger->critical('Error message', ['exception' => $e]);
        
    }
    }
}
