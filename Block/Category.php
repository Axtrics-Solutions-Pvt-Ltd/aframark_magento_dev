<?php
/**
 * Copyright © 2020 Aframark. All rights reserved.
 */
namespace Axtrics\Aframark\Block;
class Category extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Axtrics\Aframark\Model\AframarkManagement
     */
	protected $_aframodel;
	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Axtrics\Aframark\Model\AframarkManagement $aframodel
	)
	{
		 $this->_aframodel = $aframodel;
		parent::__construct($context);
	}

	/**
	 * Function for getting aframark table data
	 * @return object
	 */
	public function getAfraModel()
	{
		return $this->_aframodel;
	}
}
