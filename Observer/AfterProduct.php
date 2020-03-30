<?php
namespace Axtrics\Aframark\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
/**
 * Class AfterProduct
 * @package Axtrics\Aframark\Observer
 */
class AfterProduct implements ObserverInterface
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
    protected $helperblock;
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
            \Magento\Framework\View\Layout $layout,
            \Magento\Framework\UrlInterface $frontUrlModel,
            \Axtrics\Aframark\Block\Data $helperBlock
            )
            {
            $this->layout = $layout;
            $this->_curl = $curl;
            $this->_aframodel = $aframodel;
            $this->_afra = $afra;
            $this->productRepository = $productRepository;
            $this->_storeManager = $storeManager;
            $this->_category = $category;
            $this->frontUrlModel = $frontUrlModel;
            $this->helperblock = $helperBlock;

            }
           
    public function execute(Observer $observer)
    {
    	try{

    	$param = $observer->getEvent()->getProduct();
        if($param->isObjectNew()==1)
        {
            $action="Added";
        }
        else
        {
            $action="Updated";
        }
        $storeId = $this->_storeManager->getDefaultStoreView()->getStoreId();
  		$product=$this->productRepository->getById($param->getId());
  		$app_data=$this->_afra->getCollection()->getFirstItem();
        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
        $producturl=$this->frontUrlModel->getUrl('catalog/product/view',[ '_scope' => $storeId
            ,'id'=>$routeParams['id'],'s'=>$routeParams['s'], '_nosid' => true ]);
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
                        $image_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
                        $cats=$product->getCategoryIds();
                        $categorys=array();
                        if(count($cats) )
                       	 {
				        	foreach ($cats as $cat) 
				        		{
				            
				             $_category =  $this->_category->load($cat);
				             $categorys[]= $_category->getName();
				   
				        		}
						 }
  		 $product_collections=array(
                        'id'=>$product->getId(),
                        'title'=>$product->getName(),
                        'sku'=>$product->getSku(),
                        'image'=>$image_url,
                        'category'=>$categorys,
                        'mpn'=>$product[$mpn],
                        'upc'=>$product[$upc],
                        'ean'=>$product[$ean],
                        'isbn'=>$product[$isbn],
                        'price'=> $product->getPrice(),
                        'url'=> $producturl,

                   );

  		 $responsedata=array( 'action' => $action,'status' => 200,  'merchant_code'=>$app_data['merchant_code'],
                    'products' => $product_collections);
        
       
    	$url=$this->helperblock->getAfraUrl();
    	$this->_curl->post($url, $responsedata);
    	$response = $this->_curl->getBody();

    	
    }
    catch(\Exception $e){
$this->logger->critical($e->getMessage());
    
    }
			return ; 
        
    }
}
