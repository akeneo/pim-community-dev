<?php

namespace Pim\Bundle\ReferenceDataBundle\Enrich\Provider\Filter;

use Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Filter provider for reference data
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataFilterProvider implements FilterProviderInterface
{
    /** @var array */
    protected $filters = [
        'pim_reference_data_simpleselect' => [
            'product-export-builder' => 'akeneo-attribute-select-reference-data-filter'
        ],
        'pim_reference_data_multiselect' => [
            'product-export-builder' => 'akeneo-attribute-select-reference-data-filter'
        ]
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
