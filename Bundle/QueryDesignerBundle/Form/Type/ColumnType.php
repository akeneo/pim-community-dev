<?php

namespace Oro\Bundle\QueryDesignerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class ColumnType extends AbstractType
{
    const NAME = 'oro_query_designer_column';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', $builder->getOption('column_choice_type'), array('required' => true))
            ->add('label', 'text', array('required' => true))
            ->add('sorting', 'oro_sorting_choice', array('required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Oro\Bundle\QueryDesignerBundle\Model\Column',
                'intention'          => 'query_designer_column',
                'column_choice_type' => 'oro_entity_field_choice',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
