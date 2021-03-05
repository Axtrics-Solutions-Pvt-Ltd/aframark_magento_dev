<?php
namespace Axtrics\Aframark\Model;

class Aframark extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'aframark_settings';

    protected $_cacheTag = 'aframark_settings';

    protected $_eventPrefix = 'aframark_settings';

    protected function _construct()
    {
        $this->_init(\Axtrics\Aframark\Model\ResourceModel\Aframark::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
