<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Oro\Bundle\PimFilterBundle\Form\Type\DateTimeRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class DateTimeRangeFilterType extends AbstractType
{
    const TYPE_BETWEEN = DateRangeFilterType::TYPE_BETWEEN;
    const TYPE_NOT_BETWEEN = DateRangeFilterType::TYPE_NOT_BETWEEN;
    const NAME = 'oro_type_datetime_range_filter';

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
        return DateRangeFilterType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'field_type' => DateTimeRangeType::class
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
        $widgetOptions = ['firstDay' => 0];
        $view->vars['widget_options'] = array_merge($widgetOptions, $options['widget_options']);
    }
}
