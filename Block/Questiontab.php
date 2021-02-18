<?php

/**
 * Copyright © 2020 Aframark . All rights reserved.
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
	 * Constructor.
	 *
	 * @param Magento\Framework\HTTP\Client\Curl $curl
	 */
	protected $_storeManager;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Axtrics\Aframark\Model\Aframark $aframark,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		$this->_registry = $registry;
		$this->_aframark = $aframark;
		$this->curl = $curl;
		$this->_storeManager = $storeManager;
		$this->scopeConfig = $scopeConfig;
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
		$seturl = $this->AframarkUrl();
		$url = $seturl . "/api/allqa/" . $appdata['merchant_code'] . "/" . $product->getSku() . "/" . $_SERVER['HTTP_HOST'] . "?api_token=" . $token . $appdata['store_token'] . "";
		$this->curl->get($url);
		$result = $this->curl->getBody();
		return $result;
	}
	public function AframarkUrl()
	{
		return $this->getLayout()->createBlock('Axtrics\Aframark\Block\Data')->AframarkUrl();
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


	//Find User System APi 
	public function getUserip()
	{
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) { //Find Cloud Api 
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
			$_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		$client  = @$_SERVER['HTTP_CLIENT_IP']; // Find CLient APi
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
		if (filter_var($client, FILTER_VALIDATE_IP)) {
			$ip = $client;
		} elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
			$ip = $forward;
		} else {
			$ip = $remote;
		}
		return $ip;
	}
}
