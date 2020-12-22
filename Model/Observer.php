<?php

/**
 * Copyright © 2020 Axtrics. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Axtrics\Aframark\Model;
use Magento\Framework\DB\Adapter\LockWaitException;
/**
 * Simple Google Shopping observer
 */
class Observer 
{
      /**
     * Store Manager Interface
     * @var storeManager
     */
    private $_storeManager;
      /**
     * ObjectManagerInterface
     * @var objectmanager
     */
    protected $_objectManager;
    protected $_pageFactory;
    protected $coreDate = null;
    protected $_logger = null;

	public function __construct(
        \Axtrics\Aframark\Logger\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Axtrics\Aframark\Model\ResourceModel\Cron\CollectionFactory $cronCollection,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
        $this->_pageFactory = $pageFactory;
        $this->_logger = $logger;
        $this->coreDate = $coreDate;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->productCollection = $productCollectionFactory;
        $this->cronCollection    = $cronCollection;
        $this->scopeConfig = $scopeConfig;
    }

    public function SyncProducts(\Magento\Cron\Model\Schedule $schedule)
    {
        try {
        if( $this->jobHasAlreadyBeenRun('aframark_cron_import')){
            $this->_logger->notice("-------------------- CRON Already Running --------------------");
            return;
           }
        $limit=50;
        $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');
        $app_data=$model->getCollection()->getFirstItem();
        $app_data_update = $model->load($app_data['app_id']);

        $this->_logger->notice("-------------------- CRON Startted --------------------");
        $log[] = "-------------------- CRON PROCESS --------------------";
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
   $Cronfeed= $this->scopeConfig->getValue('Axtrics_Aframark_config/cron_mapping_setting/aframark_cron', $storeScope);
   $cron = [];
   $cron['current']['localDate'] = $this->coreDate->date('l Y-m-d H:i:s');
   $cron['current']['gmtDate'] = $this->coreDate->gmtDate('l Y-m-d H:i:s');
   $cron['current']['localTime'] = $this->coreDate->timestamp();
   $cron['current']['gmtTime'] = $this->coreDate->gmtTimestamp();
   $cronExpr = json_decode($Cronfeed);
   $i = 0;
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
                        $product_collections=array();
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
                        $this->_logger->log('600', print_r($product_collections, true));
                    }
            }  
            }
        }
    }
    $this->_logger->info('Cron Finished=============>');
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
        
        return ($currentRunningJob->getSize()) ? true : false;
    }
}