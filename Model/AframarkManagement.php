<?php
namespace Axtrics\Aframark\Model;

use Axtrics\Aframark\Api\CatelogProductInterface as AframarkApiInterface;
use Magento\Framework\App\RequestInterface;
class AframarkManagement implements AframarkApiInterface {

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /*
    * Json response set variable declare
    */
    protected $resultJsonFactory;
    /*
    * Product collection variable declare
    */
    protected $productCollection;
    protected $_customer;
    protected $order;
    private $_storeManager;
    protected $address;
    public function __construct(
        RequestInterface $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
          \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
         \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $address,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->productCollection = $productCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_objectManager = $objectManager;
        $this->logger = $logger;
        $this->_customer = $customers;
        $this->_customerFactory = $customerFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->order = $order;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_storeManager = $storeManager;
        $this->_countryFactory = $countryFactory;
    }

    /**
     * Updates the specified product from the request payload.
     *
     * @api
     * @param mixed $products
     * @return boolean
     */
    //Token Generation
    public function tokenGeneration()
    {
        $data =$this->request->getPostValue();
        $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');
        $app_data=$model->getCollection()->getFirstItem();
        $app_data_update = $model->load($app_data['app_id']);
        $resultJson = $this->resultJsonFactory->create();
        $objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $date = $objDate->gmtDate();
        $response=array();
        if($data)
        {

            if ((isset($data['app_key']) && isset($data['secret_key']))&& $app_data['app_key']==$data['app_key'] && $app_data['secret_key']==$data['secret_key']) 
            {
                if (empty($app_data['store_token'])) 
                {
                    $bytes = random_bytes(16);
                    $token=bin2hex($bytes);
                    $app_data_update->setData("store_token",$token);
                    $app_data_update->setData("store_connected",0);
                    $app_data_update->setData("last_connection_response_on",$date);
                    $app_data_update->setData("merchant_code",$data['merchant_code']);
                    $app_data_update->save();
                    $response[]=array( 'status' => 200,
                    'message' => 'Token Generated','credentials' => $app_data_update->getData());
                    
                }
                else
                {
                    $app_data_update->setData("last_connection_response_on",$date);
                    
                    $app_data_update->save();
                    $response[]=array( 'status' => 200,
                    'message' => 'Token Already Exists','credentials' => $app_data->getData());     
                }
                 
                    
            }elseif (empty($data['app_key'])) {
                $response[]=array( 'status' => 400,
                    'message' => 'The request is missing the app_key'); 
            }
            elseif (empty($data['secret_key'])) {
                $response[]=array( 'status' => 400,
                    'message' => 'The request is missing the secret_key'); 
            }   
            else
            {
                 $response[] =array( 'status' => 401,
                    'message' => 'Unauthorized Access'); 
            }
        }
        else
        {
             $response[]=array( 'status' => 204,
                    'message' => 'Empty parameters'); 
        }
        
        return $response;
    }

        //Counts the number of products
        public function countProduct()
        {
        $data =$this->request->getPostValue();
        $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');
        $app_data=$model->getCollection()->getFirstItem();
        $app_data_update = $model->load($app_data['app_id']);
        $objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $date = $objDate->gmtDate();
        if ($data) 
        {
            if ($app_data['store_token']==$data['token']) 
            {
            $response=array();
            $data =$this->request->getPostValue();
            $collection = $this->productCollection->create();
            $collection=$collection->load();
            $count= $collection->count();
            $app_data_update->setData("last_connection_response_on",$date);
            $app_data_update->save();
            $response[]=array( 'status' => 200,
                    'count' => $count);
            }
            else
            {
            $response[]=array( 'status' => 401,
                    'message' => 'Unauthorized Access'); 
            }
        }
        else
        {
           $response[]=array( 'status' => 204,
                    'message' => 'Please enter token'); 
        }

            return $response;
        }

        //Get Collection According offset and limit
        public function getCollection()
        {
            $data =$this->request->getPostValue();
            $params =$this->request->getParams();
            $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');
            $app_data=$model->getCollection()->getFirstItem();
            $app_data_update = $model->load($app_data['app_id']);
            $objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
            $date = $objDate->gmtDate();
            $response=array();
            if ($params) 
            {
                if ($data) 
                {
                if ($app_data['store_token']==$data['token'])
                {
                    $collection = $this->productCollection->create();
                    $collection=$collection->load();
                    $collection=$collection->setPageSize($params['limit']);
                    $collection=$collection->setCurPage($params['offset']);
                    foreach ($collection->getData() as $product) {
                        $productData = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product['entity_id']);
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
                        $image_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$productData->getImage();
                        $cats=$productData->getCategoryIds();
                        $categorys=array();
                        if(count($cats) ){
        foreach ($cats as $cat) {
             $_category =  $this->_objectManager->create('Magento\Catalog\Model\Category')->load($cat);
             $categorys[]= $_category->getName();
 
        }
   
}


                         $product_collections[]=array(
                        'id'=>$product['entity_id'],
                        'title'=>$productData->getName(),
                        'sku'=>$product['sku'],
                        'image'=>$image_url,
                        'category'=>$categorys,
                        'mpn'=>$productData[$mpn],
                        'upc'=>$productData[$upc],
                        'ean'=>$productData[$ean],
                        'isbn'=>$productData[$isbn],
                        'price'=> $productData->getPrice(),
                        'url'=> $productData->getProductUrl(),

                   );
                    }
                   
                    $app_data_update->setData("last_connection_response_on",$date);
                    $app_data_update->save();
                    $response[]=array( 'status' => 200,
                    'products' => $product_collections); 
                }
                else
                {
                    $response[]=array( 'status' => 401,
                    'message' => 'Unauthorized Access'); 
                }
            }
            else
            {
                $response[]=array( 'status' => 204,
                    'message' => 'Please enter token'); 
            }
            }
            else
            {
                 $response[]=array( 'status' => 204,
                    'message' => 'Empty parameters'); 
            }

           return $response;
        }
        //Get customers count
        public function countCustomer()
        {
            $data =$this->request->getPostValue();
            $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');
            $app_data=$model->getCollection()->getFirstItem();
            $app_data_update = $model->load($app_data['app_id']);
            $objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
            $date = $objDate->gmtDate();
            $fromDate = date('Y-m-d H:i:s', strtotime('-2 month'));
            $toDate = $date;
            $orders= $this->order->getCollection() 
            ->setOrder('entity_id','DESC')
            ->addAttributeToFilter('created_at', array(
                                'from' => $fromDate,
                                'to' => $toDate,
                                'date' => true,
                                ));   
            $response=array();  
             if ($data) 
                {
                if ($app_data['store_token']==$data['token'])
                {
                    $app_data_update->setData("last_connection_response_on",$date);
                    $app_data_update->save();
                    $response[]=array( 'status' => 200,
                    'count' => count($orders)); 
                }
                else
                {
                    $response[]=array( 'status' => 401,
                    'message' => 'Unauthorized Access'); 
                }
            }
            else
            {
                $response[]=array( 'status' => 204,
                    'message' => 'Please enter token'); 
            }
            
            return $response;
        }
         //Get customers collection
        public function customerCollection()
        {
            $data =$this->request->getPostValue();
            $params =$this->request->getParams();
            $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');
            $app_data=$model->getCollection()->getFirstItem();
            $app_data_update = $model->load($app_data['app_id']);
            $customer=$this->_customerFactory->create();
            $response=array();
            
            $objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
            $date = $objDate->gmtDate();
            $fromDate = date('Y-m-d H:i:s', strtotime('-2 month'));
            $toDate = $date;
            $response=array();
            $orders= $this->order->getCollection();  
            $orders=$orders->setOrder('entity_id','DESC')
             ->addAttributeToFilter('created_at', array(
                                'from' => $fromDate,
                                'to' => $toDate,
                                'date' => true,
                                ));   
            $orders=$orders->setPageSize($params['limit']);
            $orders=$orders->setCurPage($params['offset']);
                  

            if ($params) 
            {
             if ($data) 
                {
                if ($app_data['store_token']==$data['token'])
                    {
                foreach ($orders as $orderdata) 
                    {
                    $orders= $this->order->load($orderdata['entity_id']);
                    $orderItems = $orders->getAllVisibleItems();
                    $deta = $orderdata->getShippingAddress()->getData();
                    $countryCode = $deta['country_id'];
                    $country = $this->_countryFactory->create()->loadByCode($countryCode);
                    $country=$country->getName();
                        $items=array();
                    if($orders->getCustomerId() === NULL)
                        {

                        $firstname = $orders->getBillingAddress()->getFirstname();
                        $lastname = $orders->getBillingAddress()->getLastname();
                        }
                    else
                        {
                        $customer  = $this->_customer->load($orders->getCustomerId());
                    
                        $firstname=$customer->getFirstname();
                        $lastname  = $customer->getLastname();
                        }
                        foreach ($orderItems as $listitems) 
                        {
                        $getproduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($listitems['product_id']);

                           $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
                                $producturl=$getproduct->getProductUrl();
                            $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $getproduct->getImage();
                
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
                            $items[]=array('id'=>$listitems['item_id'],'title'=>$getproduct['name'],'image'=>$productImageUrl,'parent_sku'=>$getproduct->getSku(),'sku'=>$listitems['sku'],'upc'=>$getproduct[$upc],'ean'=>$getproduct[$ean],'mpn'=>$getproduct[$mpn],'isbn'=>$getproduct[$isbn],'url'=>$producturl);
                        }
                        
        $dataa[]=array('id' => $orderdata->getIncrementId(),'created_at'=>$orderdata['created_at'],'customer'=>array('email'=>$orderdata['customer_email'],'first_name'=>$firstname,'last_name'=>$lastname,'country'=>$country),'line_items' =>$items);
                    }
                   
                        $response[]=array( 'status' => 200,
                    'orders' => $dataa);
                        $app_data_update->setData("last_connection_response_on",$date);
                        $app_data_update->save();
                    }
                else
                {
                    $response[]=array( 'status' => 401,
                    'message' => 'Unauthorized Access'); 
                }
            }
            else
            {
                $response[]=array( 'status' => 204,
                    'message' => 'Please enter token'); 
            }
            }
        else
        {
                $response[]=array( 'status' => 204,
                    'message' => 'Empty parameters');
        }
            
            return $response;
        }

        /* Events to update data  */
         public function customEvents($data)
        {
            print_r($data->getId());
        }
    /* log for an API */
    public function writeLog($log)
    {
        $this->logger->info($log);
    }
}
