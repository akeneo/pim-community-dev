<?php

namespace Oro\Bundle\PimFilterBundle\Form\Type;

use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeRangeType extends AbstractType
{
    const NAME = 'pim_type_datetime_range';

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
        return DateRangeType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'field_type'    => DateTimeType::class,
                'field_options' => [
                    'format'        => LocalizerInterface::DEFAULT_DATETIME_FORMAT,
                    'view_timezone' => null,
                ],
            ]
        );
    }
}
