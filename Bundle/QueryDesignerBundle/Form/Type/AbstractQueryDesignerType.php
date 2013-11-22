<?php

namespace Oro\Bundle\QueryDesignerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Oro\Bundle\QueryDesignerBundle\Model\QueryDesigner;

abstract class AbstractQueryDesignerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('definition', 'hidden', array('required' => false))
            ->add('filters_logic', 'text', array('required' => false, 'mapped' => false));

        $factory = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                $form = $event->getForm();
                /** @var AbstractQueryDesigner $data */
                $data = $event->getData();

                $form->add(
                    $factory->createNamed(
                        'column',
                        'oro_query_designer_column',
                        null,
                        array(
                            'mapped'             => false,
                            'column_choice_type' => $form->getConfig()->getOption('column_column_choice_type'),
                            'entity'             => $data ? $data->getEntity() : null,
                            'auto_initialize'    => false
                        )
                    )
                );
                $form->add(
                    $factory->createNamed(
                        'filter',
                        'oro_query_designer_filter',
                        null,
                        array(
                            'mapped'             => false,
                            'column_choice_type' => $form->getConfig()->getOption('filter_column_choice_type'),
                            'entity'             => $data ? $data->getEntity() : null,
                            'auto_initialize'    => false
                        )
                    )
                );
            }
        );
    }

    /**
     * Gets the default options for this type.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return
            array(
                'column_column_choice_type' => 'oro_entity_field_choice',
                'filter_column_choice_type' => 'oro_entity_field_choice'
            );
    }
}
