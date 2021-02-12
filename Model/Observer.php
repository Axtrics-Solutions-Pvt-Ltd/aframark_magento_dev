<?php

/**
 * Copyright © 2020 Axtrics. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Axtrics\Aframark\Model;
use Magento\Framework\DB\Adapter\LockWaitException;
/**
 * Aframark Cron observer
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
     /**
     * @var curl
     */
    protected $_curl;
     /**
     * @param Logger $logger
     */
    protected $helperblock;
    protected $coreDate = null;
    protected $_logger = null;

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Axtrics\Aframark\Logger\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Axtrics\Aframark\Model\ResourceModel\Cron\CollectionFactory $cronCollection,
        \Axtrics\Aframark\Block\Data $helperBlock)
    {
        $this->_curl = $curl;
        $this->_logger = $logger;
        $this->coreDate = $coreDate;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->productCollection = $productCollectionFactory;
        $this->cronCollection    = $cronCollection;
        $this->scopeConfig = $scopeConfig;
        $this->helperblock = $helperBlock;
    }

    public function SyncProducts(\Magento\Cron\Model\Schedule $schedule)
    {
     $this->_logger->notice("-------------------- CRON Started Running--------------------");
        try {
            
           
        if( $this->jobHasAlreadyBeenRun('aframark_cron_import')){
            $this->_logger->notice("-------------------- CRON Already Running --------------------");
            return;
           }
        $limit=50;
        $this->_logger->notice("-------------------- CRON Started --------------------");
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
            $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');

            $app_data=$model->getCollection()->getFirstItem();
            $app_data_update = $model->load($app_data['app_id']);

            $collection =  $this->productCollection->create();
            $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

            $count= $collection->getSize();
            $counttoken = substr(md5(microtime()),rand(0,26),5);
            $countarray=array('count' => $count);
            $urll=$this->helperblock->getProductCountCronUrl()."?api_token=".$counttoken.$app_data['store_token'];
            $this->_curl->post($urll, $countarray);
            $countresponse = $this->_curl->getBody();
            $countarray=json_decode($countresponse);
            $countarray=json_decode($countresponse);
            if ($countarray == "Success") {
                $this->_logger->info("Count Success".$countresponse);
            }
            else
            {
                $this->_logger->info("Count Error".$countresponse);
            }
           
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
                            if(!empty($cats) ){
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
                            'mpn'=>!empty($productData[$mpn])?$productData[$mpn]:'',
                            'upc'=>!empty($productData[$upc])?$productData[$upc]:'',
                            'ean'=>!empty($productData[$ean])?$productData[$ean]:'',
                            'isbn'=>!empty($productData[$isbn])?$priceroductData[$isbn]:'',
                            'price'=> $productData->getPrice(),
                            'url'=> $productData->getProductUrl(),
    
                       );
                    
                        }
                        $token = substr(md5(microtime()),rand(0,26),5);
                        $responsedata=array('products' => $product_collections);
                        $url=$this->helperblock->getProductCronUrl()."?api_token=".$token.$app_data['store_token'];
                        $this->_curl->post($url, $responsedata);
                        $response = $this->_curl->getBody();
                        $responsearray=json_decode($response, TRUE);
                        $this->_logger->info("Product Cron Sucess".$response);
                        if (!empty($responsearray)) {
                        if ($responsearray[0]['status'] == "200") 
                        {
                        $this->_logger->info("Product Cron Sucess".$response);

                        }
                        else
                        {
                        $this->_logger->info("Product Cron Fail".$response);
                        }
                        }
                        else
                        {
                        $this->_logger->info("Empty Response for sku".$product->getSku());
                        }
    
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
