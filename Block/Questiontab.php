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

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Axtrics\Aframark\Model\Aframark $aframark,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Magento\Store\Model\StoreManagerInterface $storeManager)
	{
		$this->_registry = $registry;
		$this->_aframark = $aframark;
		$this->curl = $curl;
		$this->_storeManager = $storeManager;
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
    public function getAskQuestion($product,$appdata,$token)
    {
    	$url="https://my.aframark.com/api/allqa/".$appdata['merchant_code']."/".$product->getSku()."/".$_SERVER['HTTP_HOST']."?api_token=".$token.$appdata['store_token']."";
		$this->curl->get($url);
		$result = $this->curl->getBody();
		return $result;
    } 
}
