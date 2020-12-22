<?php
namespace Axtrics\Aframark\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context as Template_Context;
/**
 * Class Cron
 * @package Axtrics\Aframark\Block\Cron
 */
class Cron extends Field
{
    protected $_template = 'Axtrics_Aframark::cron.phtml';
    public function __construct(
        Template_Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []

    ) {

        parent::__construct($context,$data);
        $this->_context = $context;
        $this->scopeConfig = $scopeConfig;

    }
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getMagentoMode() {
        return $this->_appState->getMode();
    }
   public function getSGSCronExpr(){
       
     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
     return $this->scopeConfig->getValue('Axtrics_Aframark_config/cron_mapping_setting/aframark_cron', $storeScope);
   }
    /**
     * @return string
     */
    public function getMagentoPath() {
        return $this->getRootDirectory()->getAbsolutePath();
    }
}