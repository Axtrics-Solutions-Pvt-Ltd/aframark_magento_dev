<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Axtrics\Aframark\Model\Config\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Live')], ['value' => 0, 'label' => __('Test')],['value' => 2, 'label' => __('Developer')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Test'), 1 => __('Live'),2 => __('Developer')];
    }
}
