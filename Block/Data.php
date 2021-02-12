<?php
/**
 * Copyright © 2020 Aframark . All rights reserved.
 */
namespace Axtrics\Aframark\Block;
class Data extends \Magento\Framework\View\Element\Template	
{
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	\Magento\Framework\View\Element\Template\Context $context
	)
	{
		$this->scopeConfig = $scopeConfig;
		parent::__construct($context);
	}

	/**
	 * Function for getting aframark url
	 * @return string
	 */
	public function getAfraUrl()
	{
		$seturl=$this->AframarkUrl();
		$url=$seturl."/webhook/magento";
		return $url;
	}

	/**
	 * Function for getting cron aframark url
	 * @return string
	 */
	public function getProductCronUrl()
	{
		$seturl=$this->AframarkUrl();
		$cronurl=$seturl."/api/m2/products/list";
		return $cronurl;
	}

	/**
	 * Function for getting cron aframark url
	 * @return string
	 */
	public function getProductCountCronUrl()
	{
		$seturl=$this->AframarkUrl();
		$croncounturl=$seturl."/api/m2/products/count";
		return $croncounturl;
	}
	
	public function AframarkUrl(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$mode= $this->scopeConfig->getValue('Axtrics_Aframark_config/connection_setting/mode', $storeScope);
		if($mode=='1'){
			$url="https://my.aframark.com";
		}
		elseif($mode=='2'){
			$url="https://dev.aframark.com";
		}else{
			$url="https://sandbox.aframark.com";
		}
		return $url;
	}
}
