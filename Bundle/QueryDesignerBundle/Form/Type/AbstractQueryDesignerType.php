<?php

namespace Oro\Bundle\QueryDesignerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Oro\Bundle\QueryDesignerBundle\Model\QueryDesigner;
use Oro\Bundle\QueryDesignerBundle\Validator\Constraints\FilterLogicConstraint;

abstract class AbstractQueryDesignerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('definition', 'hidden', array('required' => false))
            ->add(
                'filters_logic',
                'text',
                array(
                    'constraints' => array(
                        new FilterLogicConstraint(),
                    ),
                    'required' => false,
                    'mapped' => false
                )
            );

        $factory = $builder->getFormFactory();

        $addFields = function ($form, $entity = null) use ($factory) {
            $form->add(
                $factory->createNamed(
                    'column',
                    'oro_query_designer_column',
                    null,
                    array(
                        'mapped'             => false,
                        'column_choice_type' => $form->getConfig()->getOption('column_column_choice_type'),
                        'entity'             => $entity ? $entity : null,
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
                        'entity'             => $entity ? $entity : null,
                        'auto_initialize'    => false
                    )
                )
            );
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($addFields) {
                $form = $event->getForm();
                /** @var AbstractQueryDesigner $data */
                $data = $event->getData();

                $addFields($form, $data ? $data->getEntity() : null);


            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($addFields) {
                $form = $event->getForm();
                /** @var AbstractQueryDesigner $data */
                $data = $event->getData();
                $addFields($form, $data ? $data['entity'] : null);
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $filterLogic = $form['filters_logic']->getData();
                $definition = $form['definition']->getData();

                $definition = json_decode($definition);

                $digits = [];
                preg_match_all('!\d+!', $filterLogic, $digits);
                if (isset($digits[0]) && !empty($digits[0])) {
                    $maxDigit = max($digits[0]);
                    $filtersCount = count($definition->filters);

                    if ($maxDigit > $filtersCount) {
                        $form['filters_logic']->addError(
                            new FormError(
                                'You use extra filters'
                            )
                        );
                    }

                    for ($i = 1; $i<=$filtersCount; $i++) {
                        if (!in_array($i, $digits[0])) {
                            $form['filters_logic']->addError(
                                new FormError(
                                    'You use not all filters'
                                )
                            );
                        }
                    }
                }
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
