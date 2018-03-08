<?php

namespace Pim\Bundle\EnrichBundle\Provider\Filter;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Filter provider for attributes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseFilterProvider implements FilterProviderInterface
{
    /** @var array */
    protected $filters = [
        AttributeTypes::BOOLEAN => [
            'product-export-builder' => 'akeneo-attribute-boolean-filter',
            'product-grid' => 'akeneo_attribute_boolean_filter'
        ],
        AttributeTypes::TEXT => [
            'product-export-builder' => 'akeneo-attribute-string-filter',
            'product-grid' => 'akeneo_attribute_string_filter'
        ],
        AttributeTypes::NUMBER => [
            'product-export-builder' => 'akeneo-attribute-number-filter',
            'product-grid' => 'akeneo_attribute_number_filter'
        ],
        AttributeTypes::TEXTAREA => [
            'product-export-builder' => 'akeneo-attribute-string-filter',
            'product-grid' => 'akeneo_attribute_string_filter'
        ],
        AttributeTypes::IDENTIFIER => [
            'product-export-builder' => 'akeneo-attribute-identifier-filter',
            'product-grid' => 'akeneo_attribute_identifier_filter'
        ],
        AttributeTypes::METRIC => [
            'product-export-builder' => 'akeneo-attribute-metric-filter',
            'product-grid' => 'akeneo_attribute_metric_filter'
        ],
        AttributeTypes::PRICE_COLLECTION => [
            'product-export-builder' => 'akeneo-attribute-price-collection-filter',
            'product-grid' => 'akeneo_attribute_price_collection_filter'
        ],
        AttributeTypes::IMAGE => [
            'product-export-builder' => 'akeneo-attribute-media-filter',
            'product-grid' => 'akeneo_attribute_media_filter'
        ],
        AttributeTypes::FILE => [
            'product-export-builder' => 'akeneo-attribute-media-filter',
            'product-grid' => 'akeneo_attribute_media_filter'
        ],
        AttributeTypes::OPTION_SIMPLE_SELECT => [
            'product-export-builder' => 'akeneo-attribute-select-filter',
            'product-grid' => 'akeneo_attribute_select_filter'
        ],
        AttributeTypes::OPTION_MULTI_SELECT => [
            'product-export-builder' => 'akeneo-attribute-select-filter',
            'product-grid' => 'akeneo_attribute_select_filter'
        ],
        AttributeTypes::DATE => [
            'product-export-builder' => 'akeneo-attribute-date-filter',
            'product-grid' => 'akeneo_attribute_date_filter'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getFilters($attribute)
    {
        return $this->filters[$attribute->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            in_array($element->getType(), array_keys($this->filters));
    }
}
