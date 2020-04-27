<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Axtrics\Aframark\Model\Config\Source;


/**
 * @api
 * @since 100.0.2
 */
class UPC implements \Magento\Framework\Option\ArrayInterface
{

	protected $attribute;
 public function __construct(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
    ) {
        $this->attribute = $attribute;

    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$entityTypeId='4';
    	$attribute_key[]=['value' => '0', 'label' => __('Select One')];

    	$collection = $this->attribute->getCollection()
    	->addFieldToFilter('entity_type_id', ['eq' => $entityTypeId]);
    	$attributes = $collection->toArray();
    	foreach ($attributes['items'] as $key => $value) {
    		$attribute_key[]=['value' => $value['attribute_code'], 'label' => __($value['attribute_code'])];
    	}

        return $attribute_key;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['value' => '0', 'label' => __('Select One')];
    }
}
