<?php
namespace Axtrics\Aframark\Setup;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

class Uninstall implements UninstallInterface
{
    /**
     * Module uninstall code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Data
     */
    protected $configResource;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Data $configResource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $configResource
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->configResource    = $configResource;
    }
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if ($setup->tableExists('aframark_settings')) {
            $setup->getConnection()->dropTable('aframark_settings');
        }
        $collection = $this->collectionFactory->create()
            ->addPathFilter('Axtrics_Aframark_config');
        foreach ($collection as $config) {
            $this->deleteConfig($config);
        }
        $setup->endSetup();
    }
     /**
     * @param AbstractModel $config
     * @throws \Exception
     */
    protected function deleteConfig(AbstractModel $config)
    {
        $this->configResource->delete($config);
    }
}
