<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Axtrics\Aframark\Model\Config\Source;

class mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Live')], ['value' => 0, 'label' => __('Test')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Test'), 1 => __('Live')];
    }
}
