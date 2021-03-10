<?php

/**
 * Copyright © 2021 Axtrics. All rights reserved.
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
    protected $frontUrlModel;

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Axtrics\Aframark\Logger\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Axtrics\Aframark\Model\ResourceModel\Cron\CollectionFactory $cronCollection,
        \Axtrics\Aframark\Block\Data $helperBlock,
        \Magento\Framework\UrlInterface $frontUrlModel
    ) {
        $this->_curl = $curl;
        $this->_logger = $logger;
        $this->coreDate = $coreDate;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->productCollection = $productCollectionFactory;
        $this->cronCollection    = $cronCollection;
        $this->scopeConfig = $scopeConfig;
        $this->helperblock = $helperBlock;
        $this->frontUrlModel = $frontUrlModel;
    }

    public function syncProducts(\Magento\Cron\Model\Schedule $schedule)
    {
        $time_start = microtime(true);
        $this->_logger->notice("-------------------- CRON Started Running--------------------");
        try {
            if ($this->jobHasAlreadyBeenRun('aframark_cron_import')) {
                $this->_logger->notice("-------------------- CRON Already Running --------------------");
                return;
            }
            $limit = 50;
            $this->_logger->notice("-------------------- CRON Started --------------------");
            $log[] = "-------------------- CRON PROCESS --------------------";
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            // Cron Configurations Values
            $Cronfeed = $this->scopeConfig->getValue('Axtrics_Aframark_config/cron_mapping_setting/aframark_cron', $storeScope);
            $cronenabled = $this->scopeConfig->getValue('Axtrics_Aframark_config/cron_mapping/cronenabled', $storeScope);
            if ($cronenabled == '0') {
                $this->_logger->info("Please Enable Cron from aframark configurations");
                return;
            }
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
                        //checking day and datetime
                        if (date('l', $cron['current']['gmtTime']) == $d && date('H', $cron['current']['gmtTime']) == $time[0]) {
                            $model = $this->_objectManager->create(\Axtrics\Aframark\Model\Aframark::class);
                            $app_data = $model->getCollection()->getFirstItem();
                            $collection =  $this->productCollection->create();
                            $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            $count = $collection->getSize();
                            $counttoken = $this->generateRandomString();
                            $countarray = ['count' => $count];
                            $urll = $this->helperblock->getProductCountCronUrl() . "?api_token=" . $counttoken . $app_data['store_token'];
                            $this->_curl->post($urll, $countarray);
                            $countresponse = $this->_curl->getBody();
                            $countarray = json_decode($countresponse);
                            if ($countarray == "Success") {
                                $this->_logger->info("Count Success" . $countresponse);
                            } else {
                                $this->_logger->info("Count Error" . $countresponse);
                            }

                            $collection->setCurPage(1)->setPageSize($limit)->load();
                            $numberOfPages = $collection->getLastPageNumber();
                            $productsize = 0;
                            for ($i = 1; $i <= $numberOfPages; $i++) {
                                $products =  $this->productCollection->create();
                                $products->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                                $products->setCurPage($i)->setPageSize($limit)->load();
                                $product_collections = [];
                                foreach ($products->getData() as $product) {
                                    $product_collections[]=$this->getProductData($product, $app_data);
                                }
                                $token = $this->generateRandomString();
                                $responsedata = ['products' => $product_collections];
                                $url = $this->helperblock->getProductCronUrl() . "?api_token=" . $token . $app_data['store_token'];
                                $this->_curl->post($url, $responsedata);
                                $response = $this->_curl->getBody();
                                $header = $this->_curl->getStatus();
                                $responsearray = json_decode($response, true);

                                if (!empty($responsearray)) {
                                    if ($header == '200') {
                                        $this->_logger->info("Product Cron Success" . $response);
                                    } else {
                                        $this->_logger->info("Product Cron Fail" . $response);
                                    }
                                } else {
                                    $this->_logger->info("Empty Response or response doesn't contains array for sku" . $product['sku']);
                                }
                                $productsize += count($product_collections);
                            }
                            $time_end = microtime(true);
                            $execution_time = ($time_end - $time_start) / 60;
                            $msg = 'Total ' . $productsize . ' has been inserted in ' . $execution_time . ' Mins.';
                            $this->_logger->info("Cron Batch Executed in" . $msg);
                        }
                    }
                }
            }
            
            $this->_logger->info('Cron Finished=============>');
        } catch (LockWaitException $exception) {
            $this->_logger->error('Error exception', ['exception' => $exception]);
        }
    }
    /**
     * Check if already ran for same time
     *
     * @param $jobCode
     * @return bool
     */
    private function jobHasAlreadyBeenRun($jobCode)
    {
        $currentRunningJob = $this->cronCollection->create()
            ->addFieldToFilter('job_code', $jobCode)
            ->addFieldToFilter('status', ['in' => ['pending', 'running']])
            ->getLastItem();

        return ($currentRunningJob->getSize()) ? true : false;
    }

    /**
     * Get Product Data
     *
     * @param mixed $product
     * @param mixed $app_data
     * @return array
     */
    private function getProductData($product, $app_data)
    {
        $productData = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
        $productData->load($product['entity_id']);
        if ($app_data['upc_attribute_code'] != null) {
            $upc = $app_data['upc_attribute_code'];
        } else {
            $upc = "Null";
        }
        if ($app_data['ean_attribute_code'] != null) {
            $ean = $app_data['ean_attribute_code'];
        } else {
            $ean = "Null";
        }
        if ($app_data['mpn_attribute_code'] != null) {
            $mpn = $app_data['mpn_attribute_code'];
        } else {
            $mpn = "Null";
        }
        if ($app_data['isbn_attribute_code'] != null) {
            $isbn = $app_data['isbn_attribute_code'];
        } else {
            $isbn = "Null";
        }
        $imageBaseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $image_url = $imageBaseUrl . 'catalog/product' . $productData->getImage();
        $cats = $productData->getCategoryIds();
        $categorys = [];
        if (!empty($cats)) {
            foreach ($cats as $cat) {
                $_category =  $this->_objectManager->create(\Magento\Catalog\Model\Category::class)->load($cat);
                $categorys[] = $_category->getName();
            }
        }
                                        $storeId = $this->_storeManager->getDefaultStoreView()->getStoreId();
                                        $routeParams['id'] = $productData->getId();
                                        $routeParams['s'] = $productData->getUrlKey();
                                        $producturl = $this->frontUrlModel->getUrl('catalog/product/view', [
                                            '_scope' => $storeId, 'id' => $routeParams['id'], 's' => $routeParams['s'], '_nosid' => true
                                        ]);
                                        $producturl = preg_replace('#/catalog/product/view/id/\d+/s#', '', $producturl);
                                        if (!empty($this->scopeConfig->getValue(
                                            'catalog/seo/product_url_suffix',
                                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                        ))) {
                                            $suffix=$this->scopeConfig->getValue(
                                                'catalog/seo/product_url_suffix',
                                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                            );
                                            $producturl = rtrim($producturl, '/');
                                            $producturl=$producturl.$suffix;
                                        }
                                        $product_collections = [
                                            'id' => $product['entity_id'],
                                            'title' => $productData->getName(),
                                            'sku' => $product['sku'],
                                            'image' => $image_url,
                                            'category' => $categorys,
                                            'mpn' => !empty($productData[$mpn]) ? $productData[$mpn] : '',
                                            'upc' => !empty($productData[$upc]) ? $productData[$upc] : '',
                                            'ean' => !empty($productData[$ean]) ? $productData[$ean] : '',
                                            'isbn' => !empty($productData[$isbn]) ? $productData[$isbn] : '',
                                            'price' => $productData->getPrice(),
                                            'url' => $producturl,

                                        ];
                                        return $product_collections;
    }
    /**
     * Generate random string
     * @return string
     */
    private function generateRandomString($length = 5)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
