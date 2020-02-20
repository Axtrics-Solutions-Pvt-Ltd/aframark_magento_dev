<?php
namespace Axtrics\Aframark\Block;
class Data extends \Magento\Framework\View\Element\Template	
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context
	)
	{
		parent::__construct($context);
	}

	public function getAfraUrl()
	{
		$url="http://sandbox.aframark.com/webhook/magento";
		return $url;
	}
}