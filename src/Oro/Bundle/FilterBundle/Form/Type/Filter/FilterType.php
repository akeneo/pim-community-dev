<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class FilterType extends AbstractType
{
    const NAME = 'oro_type_filter';
    const TYPE_EMPTY = 'empty';
    const TYPE_NOT_EMPTY = 'not empty';
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
    public function getBlockPrefix()
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
            $notEmptyChoice = [self::TYPE_NOT_EMPTY => $this->translator->trans('oro.filter.form.label_type_not_empty')];
            if (isset($options['field_options']['choices'])) {
                $options['field_options']['choices'] += $emptyChoice;
                $options['field_options']['choices'] += $notEmptyChoice;
            } else {
                $options['operator_choices'] += $emptyChoice;
                $options['operator_choices'] += $notEmptyChoice;
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
        $result = ['required' => false];
        if ($options['operator_choices']) {
            $result['choices'] = array_flip($options['operator_choices']);
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
        return array_merge(['required' => false], $options['field_options']);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $children = $form->all();
        $view->vars['value']['type'] = $children['type']->getViewData();
        $view->vars['value']['value'] = $children['value']->getViewData();
        $view->vars['show_filter'] = $options['show_filter'];
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'field_type'       => TextType::class,
                'field_options'    => [],
                'operator_choices' => [],
                'operator_type'    => ChoiceType::class,
                'operator_options' => [],
                'show_filter'      => false,
            ]
        )->setRequired(
            [
                'field_type',
                'field_options',
                'operator_choices',
                'operator_type',
                'operator_options',
                'show_filter'
            ]
        );
    }
}
