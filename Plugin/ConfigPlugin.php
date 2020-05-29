<?php

namespace Axtrics\Aframark\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigPlugin
{

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     *
     * @var objectmanager
     */
    protected $_objectManager;

    /**
     *
     * @var configwriter
     */
    protected $configWriter;

    public function __construct(
        RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->configWriter = $configWriter;
        $this->_objectManager = $objectManager;
        $this->request = $request;
    }


    /**
     * Config save after plugin
     */
    public function aroundSave(
        \Magento\Config\Model\Config $subject,
        \Closure $proceed
    ) {
        $data = $this->request->getPostValue();
        $model = $this->_objectManager->create('Axtrics\Aframark\Model\Aframark');
        $app_data = $model->getCollection()->getFirstItem();

        if (isset($data['config_state']['Axtrics_Aframark_config_general'])) {
            if ($data['groups']['general']['fields']['enabled']['value'] == 1) {
                $upc_code = '';
                $ean_code = '';
                $mpn_code = '';
                $isbn_code = '';

                if ($data['groups']['Gtm_mapping']['fields']['upc_code']['value'] == true) {
                    $upc_code = $data['groups']['Gtm_mapping']['fields']['aframark_upc']['value'];
                } else {
                    //Update Default UPC configration
                    $this->configWriter->save('Axtrics_Aframark_config/Gtm_mapping/aframark_upc', null, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                }
                if ($data['groups']['Gtm_mapping']['fields']['ean_code']['value'] == true) {
                    $ean_code = $data['groups']['Gtm_mapping']['fields']['aframark_ean']['value'];
                } else {
                    //Update Default Ean configration
                    $this->configWriter->save('Axtrics_Aframark_config/Gtm_mapping/aframark_ean', null, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                }
                if ($data['groups']['Gtm_mapping']['fields']['mpn_code']['value'] == true) {
                    $mpn_code = $data['groups']['Gtm_mapping']['fields']['aframark_mpn']['value'];
                } else {
                    //Update Default MPN configration
                    $this->configWriter->save('Axtrics_Aframark_config/Gtm_mapping/aframark_mpn', null, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                }
                if ($data['groups']['Gtm_mapping']['fields']['isbn_code']['value'] == true) {
                    $isbn_code = $data['groups']['Gtm_mapping']['fields']['aframark_isbn']['value'];
                } else {
                    //Update Default ISBN configration
                    $this->configWriter->save('Axtrics_Aframark_config/Gtm_mapping/aframark_isbn', null, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                }

                $appdata = array(
                    "app_key" => $data['groups']['connection_setting']['fields']['app_key']['value'],
                    "secret_key" => $data['groups']['connection_setting']['fields']['secret_key']['value'],
                    "upc_attribute_code" => $upc_code,
                    "ean_attribute_code" => $ean_code,
                    "mpn_attribute_code" => $mpn_code,
                    "isbn_attribute_code" => $isbn_code
                );

                if ($app_data->getdata('app_id')) {
                    $appdata['app_id'] = $app_data->getdata('app_id');
                }
                if ($app_data->getdata('app_key') != $appdata['app_key'] || $app_data->getdata('secret_key') != $appdata['secret_key']) {

                    $model->setData($appdata);
                    $model->setData('store_token', null);
                    $model->save();
                } else {
                    $model->setData($appdata);
                    $model->save();
                }
            } else {
                $data = "";
            }
        }
        return $proceed();
    }
}
