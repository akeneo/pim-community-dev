<?php

namespace Pim\Bundle\EnrichBundle\Provider\Filter;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Filter provider for attributes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseFilterProvider implements FilterProviderInterface
{
    /** @var array */
    protected $filters = [
        AttributeTypes::BOOLEAN => [
            'product-export-builder' => 'akeneo-attribute-boolean-filter'
        ],
        AttributeTypes::TEXT => [
            'product-export-builder' => 'akeneo-attribute-text-filter'
        ],
        AttributeTypes::NUMBER => [
            'product-export-builder' => 'akeneo-attribute-number-filter'
        ],
        AttributeTypes::TEXTAREA => [
            'product-export-builder' => 'akeneo-attribute-text-filter'
        ],
        AttributeTypes::IDENTIFIER => [
            'product-export-builder' => 'akeneo-attribute-identifier-filter'
        ],
        AttributeTypes::METRIC => [
            'product-export-builder' => 'akeneo-attribute-metric-filter'
        ],
        AttributeTypes::PRICE_COLLECTION => [
            'product-export-builder' => 'akeneo-attribute-price-collection-filter'
        ],
        AttributeTypes::IMAGE => [
            'product-export-builder' => 'akeneo-attribute-media-filter'
        ],
        AttributeTypes::FILE => [
            'product-export-builder' => 'akeneo-attribute-media-filter'
        ],
        AttributeTypes::OPTION_SIMPLE_SELECT => [
            'product-export-builder' => 'akeneo-attribute-select-filter'
        ],
        AttributeTypes::OPTION_MULTI_SELECT => [
            'product-export-builder' => 'akeneo-attribute-select-filter'
        ],
        AttributeTypes::DATE => [
            'product-export-builder' => 'akeneo-attribute-date-filter'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getFilters($attribute)
    {
        return $this->filters[$attribute->getAttributeType()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            in_array($element->getAttributeType(), array_keys($this->filters));
    }
}
