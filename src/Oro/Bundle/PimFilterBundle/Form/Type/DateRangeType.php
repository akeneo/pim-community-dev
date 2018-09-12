<?php

namespace Oro\Bundle\PimFilterBundle\Form\Type;

use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateRangeType extends AbstractType
{
    const NAME = 'pim_type_date_range';

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
        $builder->add(
            'start',
            $options['field_type'],
            array_merge(
                [
                    'required'       => false,
                    'widget'         => 'single_text',
                    'format'         => LocalizerInterface::DEFAULT_DATE_FORMAT,
                    'model_timezone' => 'UTC',
                    'view_timezone'  => 'UTC',
                ],
                $options['field_options'],
                $options['start_field_options']
            )
        );

        $builder->add(
            'end',
            $options['field_type'],
            array_merge(
                [
                    'required'       => false,
                    'widget'         => 'single_text',
                    'format'         => LocalizerInterface::DEFAULT_DATE_FORMAT,
                    'model_timezone' => 'UTC',
                    'view_timezone'  => 'UTC',
                ],
                $options['field_options'],
                $options['end_field_options']
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $children = $form->all();
        $view->vars['value']['start'] = $children['start']->getViewData();
        $view->vars['value']['end'] = $children['end']->getViewData();
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'field_type'          => DateType::class,
                'field_options'       => [],
                'start_field_options' => [],
                'end_field_options'   => [],
            ]
        );
    }
}
