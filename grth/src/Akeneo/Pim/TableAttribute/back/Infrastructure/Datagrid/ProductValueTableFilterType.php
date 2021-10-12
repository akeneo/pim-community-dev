<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Datagrid;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductValueTableFilterType extends AbstractType
{
    /** @staticvar string */
    private const NAME = 'pim_type_table_filter';

    public function getParent(): ?string
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('row');
        $builder->add('column');
        $builder->add('operator');
        $builder->add('value');
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
