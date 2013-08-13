<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SelectRowFilterType extends AbstractChoiceType
{
    const NAME = 'oro_type_selectrow_filter';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $emptyData = function ($form, $submittedData) {
            if ($submittedData === null) {
                return $submittedData;
            } elseif ($submittedData === '') {
                return array();
            }

            return null;
        };

        $builder->add('in', 'hidden', array('empty_data' => $emptyData));
        $builder->add('out', 'hidden', array('empty_data' => $emptyData));
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
