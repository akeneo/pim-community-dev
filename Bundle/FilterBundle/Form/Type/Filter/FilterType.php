<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterType extends AbstractType
{
    const NAME         = 'oro_type_filter';
    const TYPE_EMPTY   = 'empty';
    const TYPE_IN_LIST = 'in';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $emptyChoice = false;
        if (isset($options['field_options']['attr']['empty_choice'])) {
            $emptyChoice = $options['field_options']['attr']['empty_choice'];
        }
        if ($emptyChoice) {
            $emptyChoice = [self::TYPE_EMPTY => $this->translator->trans('oro.filter.form.label_type_empty')];
            if (isset($options['field_options']['choices'])) {
                $options['field_options']['choices'] += $emptyChoice;
            } else {
                $options['operator_choices'] += $emptyChoice;
            }
        }

        if (isset($options['field_options']['attr']['choice_list'])) {
            $options['operator_choices'][self::TYPE_IN_LIST] =
                $this->translator->trans('oro.filter.form.label_type_in_list');
        }

        $builder->add('type', $options['operator_type'], $this->createOperatorOptions($options));
        $builder->add('value', $options['field_type'], $this->createFieldOptions($options));
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function createOperatorOptions(array $options)
    {
        $result = array('required' => false);
        if ($options['operator_choices']) {
            $result['choices'] = $options['operator_choices'];
        }
        $result = array_merge($result, $options['operator_options']);

        return $result;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function createFieldOptions(array $options)
    {
        return array_merge(array('required' => false), $options['field_options']);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $children                     = $form->all();
        $view->vars['value']['type']  = $children['type']->getViewData();
        $view->vars['value']['value'] = $children['value']->getViewData();
        $view->vars['show_filter']    = $options['show_filter'];
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'field_type'       => 'text',
                'field_options'    => array(),
                'operator_choices' => array(),
                'operator_type'    => 'choice',
                'operator_options' => array(),
                'show_filter'      => false,
            )
        )->setRequired(
            array(
                'field_type',
                'field_options',
                'operator_choices',
                'operator_type',
                'operator_options',
                'show_filter'
            )
        );
    }
}
