<?php
namespace Axtrics\Aframark\Block;
class Category extends \Magento\Framework\View\Element\Template
{
	protected $_aframodel;
	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Axtrics\Aframark\Model\AframarkManagement $aframodel
	)
	{
		 $this->_aframodel = $aframodel;
		parent::__construct($context);
	}

	public function getAfraModel()
	{
		return $this->_aframodel;
	}
}