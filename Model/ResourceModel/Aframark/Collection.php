<?php
namespace Axtrics\Aframark\Model\ResourceModel\Aframark;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'app_id';
	protected $_eventPrefix = 'aframark_settings_collection';
	protected $_eventObject = 'aframark_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Axtrics\Aframark\Model\Aframark', 'Axtrics\Aframark\Model\ResourceModel\Aframark');
	}

}