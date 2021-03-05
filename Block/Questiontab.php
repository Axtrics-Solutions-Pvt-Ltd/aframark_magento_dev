<?php

/**
 * Copyright © 2021 Aframark . All rights reserved.
 */

namespace Axtrics\Aframark\Block;

class Questiontab extends \Magento\Framework\View\Element\Template
{
    /**
     * @var registry
     */
    protected $_registry;

    /**
     * @var aframark
     */
    protected $_aframark;
    /**
     * @var RequestInterface
     */
     protected $request;
     /**
      * @var helperBlock
      */
    protected $_helperblock;
    /**
     * Constructor.
     *
     * @param Magento\Framework\HTTP\Client\Curl $curl
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Axtrics\Aframark\Model\Aframark $aframark,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Axtrics\Aframark\Block\Data $helperblock
    ) {
        $this->_registry = $registry;
        $this->_aframark = $aframark;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->request = $httpRequest;
        $this->_helperblock = $helperblock;
        parent::__construct($context);
    }

    /**
     * Function for getting current product
     * @return object
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * Function for getting aframark model
     * @return object
     */
    public function getAfraModel()
    {
        return $this->_aframark;
    }

    /**
     * Function for getting ask a question
     * @return json
     */
    public function getAskQuestion($product, $appdata, $token)
    {
        $seturl = $this->getAframarkUrl();
        $url = $seturl . "/api/allqa/" . $appdata['merchant_code'] . "/" . $product->getSku() . "/" . $_SERVER['HTTP_HOST'] . "?api_token=" . $token . $appdata['store_token'] . "";//phpcs:ignore
        $this->curl->get($url);
        $result = $this->curl->getBody();
        return $result;
    }

    public function getAframarkUrl()
    {
        return $this->_helperblock->getAframarkUrl();
    }

    public function getWiget()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return  $this->scopeConfig->getValue('Axtrics_Aframark_config/connection_setting/display_widget', $storeScope);
    }

    public function getwidgetips()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return  $this->scopeConfig->getValue('Axtrics_Aframark_config/connection_setting/developer_ip', $storeScope);
    }

    //Find User System IP
    public function getUserip()
    {
         // Find Client IP
        if (!empty($this->request->getServer('HTTP_CLIENT_IP'))) {
            $client  = $this->request->getServer('HTTP_CLIENT_IP');
        }
        if (!empty($this->request->getServer('HTTP_X_FORWARDED_FOR'))) {
            $forward = $this->request->getServer('HTTP_X_FORWARDED_FOR');
        }
        if (!empty($this->request->getServer('REMOTE_ADDR'))) {
            $remote  = $this->request->getServer('REMOTE_ADDR');
        }
        if (null !==$this->request->getServer("HTTP_CF_CONNECTING_IP")) { //Find Cloud IP
            $remote=$this->request->getServer('REMOTE_ADDR');
            $client=$this->request->getServer('HTTP_CLIENT_IP');
            $remote = $this->request->getServer('HTTP_CF_CONNECTING_IP');
            $client = $this->request->getServer('HTTP_CF_CONNECTING_IP');
        }
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        return $ip;
    }

    /**
     * Generate random string
     * @return string
     */
    public function generateRandomString($length = 5)
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
