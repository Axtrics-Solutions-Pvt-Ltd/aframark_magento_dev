<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Axtrics\Aframark\Model\Config\Backend;

class Cron extends \Magento\Framework\App\Config\Value
{    

    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
			if(!empty($value)) {
				if ($this->isJson($value)) {
					$value = json_decode($value, true);
				} else {
					$value = unserialize($value);
				}
			}
            $this->setValue(empty($value) ? false : $value);
        }
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $this->log(\Zend\Log\Logger::INFO, "Aframark already running.");
        $this->_logger->critical('Logger File updated');
        die("oookk");
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
            $this->setValue(serialize($value));
        }
        return parent::beforeSave();
    }
	
	/**
     * Checks if the given value is json encoded
     *
     * @param  $sValue
     * @return bool
     */
    public function isJson($sValue)
    {   
        if (is_string($sValue) && is_array(json_decode($sValue, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            return true;
        }
        return false;
    }
}
