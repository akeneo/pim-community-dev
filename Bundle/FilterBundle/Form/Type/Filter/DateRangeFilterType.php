<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Oro\Bundle\FilterBundle\Form\Type\DateRangeType;

class DateRangeFilterType extends AbstractType
{
    const TYPE_BETWEEN     = 1;
    const TYPE_NOT_BETWEEN = 2;
    const TYPE_MORE_THAN   = 3;
    const TYPE_LESS_THAN   = 4;
    const NAME             = 'oro_type_date_range_filter';

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
    public function getParent()
    {
        return FilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $operatorChoices = array(
            self::TYPE_BETWEEN     => $this->translator->trans('oro.filter.form.label_date_type_between'),
            self::TYPE_NOT_BETWEEN => $this->translator->trans('oro.filter.form.label_date_type_not_between'),
            self::TYPE_MORE_THAN   => $this->translator->trans('oro.filter.form.label_date_type_more_than'),
            self::TYPE_LESS_THAN   => $this->translator->trans('oro.filter.form.label_date_type_less_than'),
        );

        $typeValues = array(
            'between'    => self::TYPE_BETWEEN,
            'notBetween' => self::TYPE_NOT_BETWEEN,
            'moreThan'   => self::TYPE_MORE_THAN,
            'lessThan'   => self::TYPE_LESS_THAN
        );

        $resolver->setDefaults(
            array(
                'field_type'       => DateRangeType::NAME,
                'operator_choices' => $operatorChoices,
                'widget_options'   => array(),
                'type_values'      => $typeValues
            )
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

        $widgetOptions                = array('firstDay' => 0);
        $view->vars['widget_options'] = array_merge($widgetOptions, $options['widget_options']);
    }
}
