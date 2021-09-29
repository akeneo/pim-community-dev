<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Datagrid;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Symfony\Component\Form\AbstractType;

class ProductValueTableFilterType extends AbstractType
{
    private const NAME = 'oro_type_text_filter';

    public function getParent(): string
    {
        // TODO This parent is too complex, should be removed CPM-377
        return FilterType::class;
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
