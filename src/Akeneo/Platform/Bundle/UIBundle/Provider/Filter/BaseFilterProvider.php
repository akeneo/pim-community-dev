<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Filter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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
            'product-export-builder' => 'akeneo-attribute-boolean-filter'
        ],
        AttributeTypes::TEXT => [
            'product-export-builder' => 'akeneo-attribute-string-filter'
        ],
        AttributeTypes::NUMBER => [
            'product-export-builder' => 'akeneo-attribute-number-filter'
        ],
        AttributeTypes::TEXTAREA => [
            'product-export-builder' => 'akeneo-attribute-string-filter'
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
