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

	public function AframarkUrl(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$mode= $this->scopeConfig->getValue('Axtrics_Aframark_config/connection_setting/mode', $storeScope);
		if(!$mode){
			$url="https://sandbox.aframark.com";
		}else{
			$url="https://my.aframark.com";
		}
		return $url;
	}
}
