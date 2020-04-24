<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\FilterProvider;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface;

/**
 * Filter provider for reference entity collections
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ReferenceEntityCollectionFilterProvider implements FilterProviderInterface
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
    public function getFilters($attribute): array
    {
        return $this->filters[$attribute->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof AttributeInterface && array_key_exists($element->getType(), $this->filters);
    }
}
