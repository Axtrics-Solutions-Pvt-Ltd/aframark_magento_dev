<?php
namespace Axtrics\Aframark\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterDeleteProduct implements ObserverInterface
{
    /**
     * @param Observer $observer
     *
     */

    /**
     * @var curl
     */
    protected $_curl;

    /**
     * Aframark model data
     * @var afra
     */
    protected $_afra;

    /**
     * @var helperblock
     */
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
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Axtrics\Aframark\Model\Aframark $afra,
        \Psr\Log\LoggerInterface $logger,
        \Axtrics\Aframark\Block\Data $helperBlock
    ) {
        $this->_curl = $curl;
        $this->_afra = $afra;
        $this->helperblock = $helperBlock;
        $this->logger = $logger;
    }
    
    public function execute(Observer $observer)
    {
        try {

            $param = $observer->getEvent()->getProduct();
            $app_data=$this->_afra->getCollection()->getFirstItem();
            $_sku = $param->getSku();
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Aframark.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($_sku);
            $product_collections=[
                        'id'=>$param->getId(),
                        'sku'=>$param->getSku(),
                   ];
        
            $responsedata=['action' => "Delete",'status' => 200,
            'merchant_code'=>$app_data['merchant_code'],
                    'products' => $product_collections];
            $url=$this->helperblock->getAfraUrl();
            $this->_curl->post($url, $responsedata);
        
            $response = $this->_curl->getBody();
        
        } catch (\Exception $e) {
            $product = false;
            $this->logger->critical('Error message', ['exception' => $e]);
        
        }
    }
}
