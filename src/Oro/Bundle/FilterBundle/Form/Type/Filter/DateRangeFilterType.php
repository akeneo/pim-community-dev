<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Oro\Bundle\PimFilterBundle\Form\Type\DateRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class DateRangeFilterType extends AbstractType
{
    const TYPE_BETWEEN = 1;
    const TYPE_NOT_BETWEEN = 2;
    const TYPE_MORE_THAN = 3;
    const TYPE_LESS_THAN = 4;
    const NAME = 'oro_type_date_range_filter';

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
    public function getParent()
    {
        return FilterType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $operatorChoices = [
            self::TYPE_BETWEEN     => $this->translator->trans('oro.filter.form.label_date_type_between'),
            self::TYPE_NOT_BETWEEN => $this->translator->trans('oro.filter.form.label_date_type_not_between'),
            self::TYPE_MORE_THAN   => $this->translator->trans('oro.filter.form.label_date_type_more_than'),
            self::TYPE_LESS_THAN   => $this->translator->trans('oro.filter.form.label_date_type_less_than'),
        ];

        $typeValues = [
            'between'    => self::TYPE_BETWEEN,
            'notBetween' => self::TYPE_NOT_BETWEEN,
            'moreThan'   => self::TYPE_MORE_THAN,
            'lessThan'   => self::TYPE_LESS_THAN
        ];

        $resolver->setDefaults(
            [
                'field_type'       => DateRangeType::class,
                'operator_choices' => $operatorChoices,
                'widget_options'   => [],
                'type_values'      => $typeValues
            ]
        );
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['type_values'] = $options['type_values'];

        $widgetOptions = ['firstDay' => 0];
        $view->vars['widget_options'] = array_merge($widgetOptions, $options['widget_options']);
    }
}
