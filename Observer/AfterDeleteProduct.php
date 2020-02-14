<?php
namespace Axtrics\Aframark\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
/**
 * Class AfterSave
 * @package Axtrics\Aframark\Observer
 */
class AfterDeleteProduct implements ObserverInterface
{
    /**
     * @param Observer $observer
     *
     */
    protected $_curl;
    protected $productRepository; 
    protected $_afra;
    private $_storeManager;
    public $_category;
/**
 * @param \Magento\Framework\HTTP\Client\Curl $curl
 */
            public function __construct(
            \Axtrics\Aframark\Model\AframarkManagement $aframodel,
            \Magento\Framework\HTTP\Client\Curl $curl,
            ProductRepositoryInterface $productRepository,
            \Axtrics\Aframark\Model\Aframark $afra,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Catalog\Model\Category $category,
            \Magento\Framework\View\Layout $layout
            )
            {
            $this->layout = $layout;
            $this->_curl = $curl;
            $this->_aframodel = $aframodel;
            $this->_afra = $afra;
            $this->productRepository = $productRepository;
            $this->_storeManager = $storeManager;
            $this->_category = $category;

            }
    
    public function execute(Observer $observer)
    {
       try{

    	$param = $observer->getEvent()->getProduct();
  		$app_data=$this->_afra->getCollection()->getFirstItem();
        $_sku = $param->getSku();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Aframark.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($_sku);

  		 // if ($app_data['upc_attribute_code']!=null) {
     //                        $upc=$app_data['upc_attribute_code'];
     //                    }
     //                    else
     //                    {
     //                        $upc="Null";
     //                    }
     //                    if ($app_data['ean_attribute_code']!=null) {
     //                        $ean=$app_data['ean_attribute_code'];
     //                    }
     //                    else
     //                    {
     //                        $ean="Null";
     //                    }
     //                    if ($app_data['mpn_attribute_code']!=null) {
     //                        $mpn=$app_data['mpn_attribute_code'];
     //                    }
     //                    else
     //                    {
     //                        $mpn="Null";
     //                    }
     //                    if ($app_data['isbn_attribute_code']!=null) {
     //                        $isbn=$app_data['isbn_attribute_code'];
     //                    }
     //                    else
     //                    {
     //                        $isbn="Null";
     //                    }

                        // $image_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
       //                  $cats=$param->getCategoryIds();
       //                  $categorys=array();

       //                  if(count($cats) )
       //                 	 {
				   //      	foreach ($cats as $cat) 
				   //      		{
				            
				   //           $_category =  $this->_category->load($cat);
				   //           $categorys[]= $_category->getName();
				   
				   //      		}
						 // }

  		 $product_collections=array(
                        'id'=>$param->getId(),
                        'sku'=>$param->getSku(),
                   );
        
  		 $responsedata=array('action' => "Delete",'status' => 200,
            'merchant_code'=>$app_data['merchant_code'],
                    'products' => $product_collections);
    	$url="http://aframark.axtrics.com/webhook/magento";
    	
    	$this->_curl->post($url, $responsedata);
    	
    	$response = $this->_curl->getBody();
    	
      
    }
    catch(\Exception $e){
$product = false;
die("errro");
    	
    }
      
			return ; 
        
    }
}
   
