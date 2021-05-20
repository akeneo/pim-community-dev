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
        // TODO Change this
        return 'akeneo-text-field';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof AttributeInterface && 'pim_catalog_table' === $element->getType();
    }
}
