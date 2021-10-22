<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Provider;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface;

final class TableFilterProvider implements FilterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFilters($attribute): array
    {
        return [
            'product-export-builder' => 'akeneo-attribute-table-filter',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof AttributeInterface && 'pim_catalog_table' === $element->getType();
    }
}
