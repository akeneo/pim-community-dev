<?php

namespace Oro\Bundle\QueryDesignerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class FilterType extends AbstractType
{
    const NAME = 'oro_query_designer_filter';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('criterion', 'text', array('required' => true));

        $factory = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                $form = $event->getForm();

                $form->add(
                    $factory->createNamed(
                        'columnName',
                        $form->getConfig()->getOption('column_choice_type'),
                        null,
                        array(
                            'required'        => true,
                            'entity'          => $form->getConfig()->getOption('entity'),
                            'with_relations'  => true,
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
                'data_class'         => 'Oro\Bundle\QueryDesignerBundle\Model\Filter',
                'intention'          => 'query_designer_filter',
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
