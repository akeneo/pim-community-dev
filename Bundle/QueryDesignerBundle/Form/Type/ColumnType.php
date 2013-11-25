<?php

namespace Oro\Bundle\QueryDesignerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ColumnType extends AbstractType
{
    const NAME = 'oro_query_designer_column';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', 'text', array('required' => true))
            ->add('sorting', 'oro_sorting_choice', array('required' => false));

        $factory = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                $form = $event->getForm();

                $form->add(
                    $factory->createNamed(
                        'name',
                        $form->getConfig()->getOption('column_choice_type'),
                        null,
                        array(
                            'required'        => true,
                            'entity'          => $form->getConfig()->getOption('entity'),
                            'with_relations'  => true,
                            'deep_level'      => 1,
                            'auto_initialize' => false
                        )
                    )
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'entity'             => null,
                'data_class'         => 'Oro\Bundle\QueryDesignerBundle\Model\Column',
                'intention'          => 'query_designer_column',
                'column_choice_type' => 'oro_entity_field_choice'
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
