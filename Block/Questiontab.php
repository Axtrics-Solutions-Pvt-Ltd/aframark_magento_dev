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

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Axtrics\Aframark\Model\Aframark $aframark)
	{
		$this->_registry = $registry;
		$this->_aframark = $aframark;

		parent::__construct($context);
	}

	 public function getCurrentProduct()
    {        
        return $this->_registry->registry('current_product');
    } 
    public function getAfraModel()
    {
    	return $this->_aframark;
    } 
}
