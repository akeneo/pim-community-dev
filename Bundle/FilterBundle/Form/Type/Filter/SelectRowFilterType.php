<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SelectRowFilterType extends AbstractChoiceType
{
    const NAME = 'oro_type_selectrow_filter';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('in', 'hidden');
        $builder->add('out', 'hidden');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return FilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'field_type'       => 'choice',
                'field_options'    => array('choices' => array()),
            )
        );
    }
}
