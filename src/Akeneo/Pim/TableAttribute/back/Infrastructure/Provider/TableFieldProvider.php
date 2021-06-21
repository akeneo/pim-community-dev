<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Provider;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;

final class TableFieldProvider implements FieldProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getField($attribute): string
    {
        return 'akeneo-table-field';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof AttributeInterface && 'pim_catalog_table' === $element->getType();
    }
}
