<?php
/**
 * Copyright © 2020 Aframark . All rights reserved.
 */
namespace Axtrics\Aframark\Block;
class Data extends \Magento\Framework\View\Element\Template	
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context
	)
	{
		parent::__construct($context);
	}

	/**
	 * Function for getting aframark url
	 * @return string
	 */
	public function getAfraUrl()
	{
		$url="https://sandbox.aframark.com/webhook/magento";
		return $url;
	}
}
