<?php
namespace Axtrics\Aframark\Model\ResourceModel;

class Aframark extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }
    
    protected function _construct()
    {
        $this->_init('aframark_settings', 'app_id');
    }
}
