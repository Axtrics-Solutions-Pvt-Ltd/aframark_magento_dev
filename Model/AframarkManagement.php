<?php

/**
 * Contributor company: Axtrics Solution Pvt Ltd.
 * Contributor Author : Shubham Kumar
 */

namespace Axtrics\Aframark\Model;

use Axtrics\Aframark\Api\CatelogProductInterface as AframarkApiInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Defines the implementaiton class of the Various api calls
 */
class AframarkManagement implements AframarkApiInterface
{
    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */

    protected $_request;

    /**
     * @var CollectionFactory
     */
    protected $_productCollection;

    /**
     * Customer Model
     * @var Customer
     */
    protected $_customer;

    /**
     * Order Model
     * @var Order
     */
    protected $_order;

    /**
     * Store Manager Interface
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * ObjectManagerInterface
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Country Factory
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * Logger
     * @var logger
     */
    protected $_logger;
    /**
     * Scope Config
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Customer Factory
     * @var CollectionFactory
     */
    protected $_customerFactory;

    public function __construct(
        RequestInterface $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_request = $request;
        $this->productRepository = $productRepository;
        $this->_productCollection = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->_logger = $logger;
        $this->_customer = $customers;
        $this->_customerFactory = $customerFactory;
        $this->_order = $order;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_countryFactory = $countryFactory;
    }

    /**
     * Get Generated token and app data
     * @return array
     */
    public function tokenGeneration()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $mode = $this->scopeConfig->getValue('Axtrics_Aframark_config/connection_setting/mode', $storeScope);
        if ($mode == '1') {
            $mode = 'Live';
        } elseif ($mode == '2') {
            $mode = 'Developer';
        } else {
            $mode = 'Test';
        }
        $data = $this->_request->getPostValue();
        $model = $this->_objectManager->create(\Axtrics\Aframark\Model\Aframark::class);
        $appData = $model->getCollection()->setPageSize(1)->setCurPage(1)->getFirstItem();
        $appDataUpdate = $model->load($appData['app_id']);
        $objDate = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $date = $objDate->gmtDate();
        $response = [];
        $appData["mode"] = $mode;
        if ($data) {
            if ((isset($data['app_key']) && isset($data['secret_key'])) && $appData['app_key'] == $data['app_key'] && $appData['secret_key'] == $data['secret_key']) {// phpcs:ignore
                if (empty($appData['store_token'])) {
                    $bytes = random_bytes(16);
                    $token = bin2hex($bytes);
                    $appDataUpdate->setData("store_token", $token);
                    $appDataUpdate->setData("store_connected", 0);
                    $appDataUpdate->setData("last_connection_response_on", $date);
                    $appDataUpdate->setData("merchant_code", $data['merchant_code']);
                    $appDataUpdate->save();
                    $response[] = [
                        'status' => 200,
                        'message' => 'Token Generated', 'credentials' => $appDataUpdate->getData()
                    ];
                } else {
                    $appDataUpdate->setData("last_connection_response_on", $date);

                    $appDataUpdate->save();
                    $response[] = [
                        'status' => 200,
                        'message' => 'Token Already Exists', 'credentials' => $appData->getData()
                    ];
                }
            } elseif (empty($data['app_key'])) {
                $response[] = [
                    'status' => 400,
                    'message' => 'The request is missing the app_key'
                ];
            } elseif (empty($data['secret_key'])) {
                $response[] = [
                    'status' => 400,
                    'message' => 'The request is missing the secret_key'
                ];
            } else {
                $response[] = [
                    'status' => 401,
                    'message' => 'Unauthorized Access'
                ];
            }
        } else {
            $response[] = [
                'status' => 204,
                'message' => 'Empty parameters'
            ];
        }

        return $response;
    }

    /**
     * Return Count of products
     * @return array
     */
    public function countProduct()
    {
        $data = $this->_request->getPostValue();
        $model = $this->_objectManager->create(\Axtrics\Aframark\Model\Aframark::class);
        $appData = $model->getCollection()->setPageSize(1)->setCurPage(1)->getFirstItem();
        $appDataUpdate = $model->load($appData['app_id']);
        $objDate = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $date = $objDate->gmtDate();
        if ($data) {
            if ($appData['store_token'] == $data['token']) {
                $response = [];
                $data = $this->_request->getPostValue();
                $collection = $this->_productCollection->create();
                $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);// phpcs:ignore
                $count = $collection->getSize();
                $appDataUpdate->setData("last_connection_response_on", $date);
                $appDataUpdate->save();
                $response[] = [
                    'status' => 200,
                    'count' => $count
                ];
            } else {
                $response[] = [
                    'status' => 401,
                    'message' => 'Unauthorized Access'
                ];
            }
        } else {
            $response[] = [
                'status' => 204,
                'message' => 'Please enter token'
            ];
        }

        return $response;
    }

    /**
     * get product data function
     * @param mixed $product
     * @param mixed $appData
     * @return array
     */
    private function getProductData($product, $appData)
    {
        $productData = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
        $productData->load($product['entity_id']);
        if ($appData['upc_attribute_code'] != null) {
            $upc = $appData['upc_attribute_code'];
        } else {
            $upc = "Null";
        }
        if ($appData['ean_attribute_code'] != null) {
            $ean = $appData['ean_attribute_code'];
        } else {
            $ean = "Null";
        }
        if ($appData['mpn_attribute_code'] != null) {
            $mpn = $appData['mpn_attribute_code'];
        } else {
            $mpn = "Null";
        }
        if ($appData['isbn_attribute_code'] != null) {
            $isbn = $appData['isbn_attribute_code'];
        } else {
            $isbn = "Null";
        }
        $imageBaseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $imageUrl =$imageBaseUrl. 'catalog/product' . $productData->getImage();
        $cats = $productData->getCategoryIds();
        $categorys = [];
        if (!empty($cats)) {
            foreach ($cats as $cat) {
                $_category =  $this->_objectManager->create(\Magento\Catalog\Model\Category::class)->load($cat);
                $categorys[] = $_category->getName();
            }
        }

        $productCollections = [
            'id' => $product['entity_id'],
            'title' => $productData->getName(),
            'sku' => $product['sku'],
            'image' => $imageUrl,
            'category' => $categorys,
            'mpn' => $productData[$mpn],
            'upc' => $productData[$upc],
            'ean' => $productData[$ean],
            'isbn' => $productData[$isbn],
            'price' => $productData->getPrice(),
            'url' => $productData->getProductUrl(),

        ];//phpcs:ignore
        return $productCollections;
    }

    /**
     * Get product collection
     * @return array
     */
    public function getCollection()
    {
        $data = $this->_request->getPostValue();
        $params = $this->_request->getParams();
        $model = $this->_objectManager->create(\Axtrics\Aframark\Model\Aframark::class);
        $appData = $model->getCollection()->setPageSize(1)->setCurPage(1)->getFirstItem();
        $appDataUpdate = $model->load($appData['app_id']);
        $objDate = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $date = $objDate->gmtDate();
        $response = [];
        if ($params && $data) {
            if ($appData['store_token'] == $data['token']) {
                $collection = $this->_productCollection->create();
                $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);// phpcs:ignore
                $collection = $collection->load();
                $collection = $collection->setPageSize($params['limit']);
                $collection = $collection->setCurPage($params['offset']);
                foreach ($collection->getData() as $product) {
                    $productCollections[] = $this->getProductData($product, $appData);
                }
                $appDataUpdate->setData("last_connection_response_on", $date);
                $appDataUpdate->save();
                $response[] = [
                    'status' => 200,
                    'products' => $productCollections
                ];
            } else {
                $response[] = [
                    'status' => 401,
                    'message' => 'Unauthorized Access'
                ];
            }
        } else {
            $response[] = [
                'status' => 204,
                'message' => 'Empty parameters'
            ];
        }

        return $response;
    }

    /**
     * Get Customer count
     * @return array
     */
    public function countCustomer()
    {
        $data = $this->_request->getPostValue();
        $model = $this->_objectManager->create(\Axtrics\Aframark\Model\Aframark::class);
        $appData = $model->getCollection()->setPageSize(1)->setCurPage(1)->getFirstItem();
        $appDataUpdate = $model->load($appData['app_id']);
        $objDate = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $date = $objDate->gmtDate();
        $fromDate = date('Y-m-d H:i:s', strtotime('-2 month'));
        $toDate = $date;
        $orders = $this->_order->getCollection()
            ->setOrder('entity_id', 'DESC')
            ->addAttributeToFilter('created_at', [
                'from' => $fromDate,
                'to' => $toDate,
                'date' => true,
            ]);
        $response = [];
        if ($data) {
            if ($appData['store_token'] == $data['token']) {
                $appDataUpdate->setData("last_connection_response_on", $date);
                $appDataUpdate->save();
                $response[] = [
                    'status' => 200,
                    'count' => count($orders)
                ];
            } else {
                $response[] = [
                    'status' => 401,
                    'message' => 'Unauthorized Access'
                ];
            }
        } else {
            $response[] = [
                'status' => 204,
                'message' => 'Please enter token'
            ];
        }

        return $response;
    }

     /**
      * get product data function
      * @param mixed $product
      * @param mixed $appData
      * @return array
      */
    private function getCustomerData($orderdata, $appData)
    {
            $orders = $this->_order->load($orderdata['entity_id']);
                        $orderItems = $orders->getAllVisibleItems();
                        $deta = $orderdata->getShippingAddress()->getData();
                        $countryCode = $deta['country_id'];
                        $country = $this->_countryFactory->create()->loadByCode($countryCode);
                        $country = $country->getName();
                        $items = [];
        if ($orders->getCustomerId() === null) {
            $firstname = $orders->getBillingAddress()->getFirstname();
            $lastname = $orders->getBillingAddress()->getLastname();
        } else {
            $customer  = $this->_customer->load($orders->getCustomerId());

            $firstname = $customer->getFirstname();
            $lastname  = $customer->getLastname();
        }
        foreach ($orderItems as $listitems) {
            $getproduct = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
            $getproduct->load($listitems['product_id']);
            $store = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore();
            $producturl = $getproduct->getProductUrl();
            $productImageBaseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $productImageUrl = $productImageBaseUrl . 'catalog/product' . $getproduct->getImage();
            if ($appData['upc_attribute_code'] != null) {
                $upc = $appData['upc_attribute_code'];
            } else {
                $upc = "Null";
            }
            if ($appData['ean_attribute_code'] != null) {
                $ean = $appData['ean_attribute_code'];
            } else {
                $ean = "Null";
            }
            if ($appData['mpn_attribute_code'] != null) {
                $mpn = $appData['mpn_attribute_code'];
            } else {
                $mpn = "Null";
            }
            if ($appData['isbn_attribute_code'] != null) {
                $isbn = $appData['isbn_attribute_code'];
            } else {
                $isbn = "Null";
            }
            $items[] = array('id' => $listitems['item_id'], 'title' => $getproduct['name'], 'image' => $productImageUrl, 'parent_sku' => $getproduct->getSku(), 'sku' => $listitems['sku'], 'upc' => $getproduct[$upc], 'ean' => $getproduct[$ean], 'mpn' => $getproduct[$mpn], 'isbn' => $getproduct[$isbn], 'url' => $producturl);//phpcs:ignore
        }

                        $dataa = array('id' => $orderdata->getIncrementId(), 'created_at' => $orderdata['created_at'], 'customer' => array('email' => $orderdata['customer_email'], 'first_name' => $firstname, 'last_name' => $lastname, 'country' => $country), 'line_items' => $items);//phpcs:ignore
                        return $dataa;
    }

    /**
     * Get customer collection and order data
     * @return array
     */
    public function customerCollection()
    {
        $data = $this->_request->getPostValue();
        $params = $this->_request->getParams();
        $model = $this->_objectManager->create(\Axtrics\Aframark\Model\Aframark::class);
        $appData = $model->getCollection()->setPageSize(1)->setCurPage(1)->getFirstItem();
        $appDataUpdate = $model->load($appData['app_id']);
        $customer = $this->_customerFactory->create();
        $response = [];

        $objDate = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $date = $objDate->gmtDate();
        $fromDate = date('Y-m-d H:i:s', strtotime('-2 month'));
        $toDate = $date;
        $response = [];
        $orders = $this->_order->getCollection();
        $orders = $orders->setOrder('entity_id', 'DESC')
            ->addAttributeToFilter('created_at', [
                'from' => $fromDate,
                'to' => $toDate,
                'date' => true,
            ]);
        $orders = $orders->setPageSize($params['limit']);
        $orders = $orders->setCurPage($params['offset']);
        if ($params) {
            if ($data) {
                if ($appData['store_token'] == $data['token']) {
                    foreach ($orders as $orderdata) {
                        $dataa[]=$this->getCustomerData($orderdata, $appData);
                    }

                    $response[] = [
                        'status' => 200,
                        'orders' => $dataa
                    ];
                    $appDataUpdate->setData("last_connection_response_on", $date);
                    $appDataUpdate->save();
                } else {
                    $response[] = [
                        'status' => 401,
                        'message' => 'Unauthorized Access'
                    ];
                }
            } else {
                $response[] = [
                    'status' => 204,
                    'message' => 'Please enter token'
                ];
            }
        } else {
            $response[] = [
                'status' => 204,
                'message' => 'Empty parameters'
            ];
        }

        return $response;
    }

    /* log for an API */
    public function writeLog($log)
    {
        $this->_logger->info($log);
    }
}
