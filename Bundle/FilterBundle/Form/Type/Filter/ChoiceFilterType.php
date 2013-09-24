<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoiceFilterType extends AbstractChoiceType
{
    const TYPE_CONTAINS = 1;
    const TYPE_NOT_CONTAINS = 2;
    const NAME = 'oro_type_choice_filter';

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
        // TODO Remove OroFilterBundle domain after translation refactoring
        $choices = array(
            self::TYPE_CONTAINS     => $this->translator->trans('label_type_contains', array(), 'OroFilterBundle'),
            self::TYPE_NOT_CONTAINS => $this->translator->trans('label_type_not_contains', array(), 'OroFilterBundle'),
        );

        $resolver->setDefaults(
            array(
                'field_type'       => 'choice',
                'field_options'    => array('choices' => array()),
                'operator_choices' => $choices,
                'populate_default'  => true
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        if (isset($options['populate_default'])) {
            $view->vars['populate_default'] = $options['populate_default'];
        }
    }
}
