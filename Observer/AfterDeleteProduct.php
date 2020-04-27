<?php
namespace Axtrics\Aframark\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
/**
 * Class AfterDeleteProduct
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
    protected $helperblock;
     /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
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
            \Psr\Log\LoggerInterface $logger,
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
            $this->helperblock = $helperBlock;
            $this->logger = $logger;
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
  		$product_collections=array(
                        'id'=>$param->getId(),
                        'sku'=>$param->getSku(),
                   );
        
  		 $responsedata=array('action' => "Delete",'status' => 200,
            'merchant_code'=>$app_data['merchant_code'],
                    'products' => $product_collections);
        $url=$this->helperblock->getAfraUrl();
    	$this->_curl->post($url, $responsedata);
    	
    	$response = $this->_curl->getBody();
    	
      
    }
    catch(\Exception $e){
$product = false;
$this->logger->critical('Error message', ['exception' => $e]);
    	
    }
      
			return ; 
        
    }
}
   
