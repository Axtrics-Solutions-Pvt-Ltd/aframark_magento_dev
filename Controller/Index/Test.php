<?php
namespace Axtrics\Aframark\Controller\Index;
use Magento\Framework\DB\Adapter\LockWaitException;

class Test extends \Magento\Framework\App\Action\Action
{

     /**
     * Store Manager Interface
     * @var storeManager
     */
    private $_storeManager;
    protected $_pageFactory;
    protected $coreDate = null;
    protected $_logger = null;
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Axtrics\Aframark\Logger\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Axtrics\Aframark\Model\ResourceModel\Cron\CollectionFactory $cronCollection,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
        $this->_pageFactory = $pageFactory;
        $this->_logger = $logger;
        $this->coreDate = $coreDate;
        $this->productCollection = $productCollectionFactory;
        $this->cronCollection    = $cronCollection;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
		return parent::__construct($context);
	}

	public function execute()
	{
        try {
  
    //    if( $this->jobHasAlreadyBeenRun('aframark_cron_import')){
    //     $this->_logger->notice("-------------------- CRON Already Running --------------------");
    //     return;
    //    }
       $limit=10;
        
        $log = [];
        $cnt = 0;
        $this->_logger->notice("-------------------- CRON Test PROCESS --------------------");
        $log[] = "-------------------- CRON Test PROCESS --------------------";
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
       $Cronfeed= $this->scopeConfig->getValue('Axtrics_Aframark_config/cron_mapping_setting/aframark_cron', $storeScope);
       $cron = [];
       $cron['current']['localDate'] = $this->coreDate->date('l Y-m-d H:i:s');
       $cron['current']['gmtDate'] = $this->coreDate->gmtDate('l Y-m-d H:i:s');
       $cron['current']['localTime'] = $this->coreDate->timestamp();
       $cron['current']['gmtTime'] = $this->coreDate->gmtTimestamp();

       $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');
            $app_data=$model->getCollection()->getFirstItem();
            $app_data_update = $model->load($app_data['app_id']);

       $cronExpr = json_decode($Cronfeed);
       $i = 0;
       echo"<pre>"; 
       print_r($cron);
       print_r($cronExpr);
       if ($cronExpr != null && isset($cronExpr->days)) {
           foreach ($cronExpr->days as $d) {
               foreach ($cronExpr->hours as $h) {
                $time = explode(':', $h);
             
                if (date('l', $cron['current']['gmtTime']) == $d && date('H', $cron['current']['gmtTime'])==$time[0]) {
                    $collection =  $this->productCollection->create();
                    $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    $collection->setCurPage(1)->setPageSize($limit)->load();
                    $numberOfPages = $collection->getLastPageNumber();
                    for ($i=1; $i<=$numberOfPages; $i++) {
                        $products =  $this->productCollection->create();
                            $products->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            $products->setCurPage($i)->setPageSize($limit)->load();
                            $product_collections =array();
                        foreach ($products->getData() as $product) {
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
                        print_r($product_collections);
                    }
               
                        $cron['tasks'][$i]['localTime'] = strtotime($this->coreDate->date('Y-m-d')) + $time[0] * 60 * 60 + $time[1] * 60;
                        $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);   
                    
                    //  if ( $currenttime== $cron['current']['localTime']) {
                        
                    //     $cron['tasks'][$i]['localTime'] = strtotime($this->coreDate->date('Y-m-d')) + $time[0] * 60 * 60 + $time[1] * 60;
                    //     $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                    //     print_r( date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']));
                    //     echo "br>";
                    //     echo strtotime($this->coreDate->date('Y-m-d')) + $time[0] * 60 * 60 + $time[1] * 60;;
                    //     echo"<br>";
                    // }

                    
                }
               // print_r($cron);
                // if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['current']['localTime'] && $done != true) {
                //     $this->_logger->notice('   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']));
                //     $log[] = '   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']) . "";
                //     $this->_logger->notice("   * Starting generation");
                //     $cnt++;
                //     break 2;
                // }
                $i++;
               }
            }
        }
    } catch (LockWaitException $exception) {
        $this->_logger->error('600', print_r($exception, true));
    }
    }
    
    /**
     * Check if already ran for same time
     *
     * @param $jobCode
     * @return bool
     */
    private function jobHasAlreadyBeenRun($jobCode)
    { $currentRunningJob = $this->cronCollection->create()
        ->addFieldToFilter('job_code',$jobCode )
        ->addFieldToFilter('status', ['in' => ['pending', 'running']])
        ->getLastItem();
        
        return ($currentRunningJob->getSize()) ? false : true;
    }
}
