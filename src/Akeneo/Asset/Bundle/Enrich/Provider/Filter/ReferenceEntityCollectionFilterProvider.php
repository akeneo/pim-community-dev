<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Enrich\Provider\Filter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface;

/**
 * Filter provider for reference entity collections
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ReferenceEntityCollectionFilterProvider implements FilterProviderInterface
{
    /** @var array */
    private $filters = [
        'akeneo_reference_entity_collection' => [
            'product-export-builder' => 'akeneo-attribute-reference-entity-collection-filter',
        ],
        'akeneo_reference_entity' => [
            'product-export-builder' => 'akeneo-attribute-reference-entity-collection-filter',
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
