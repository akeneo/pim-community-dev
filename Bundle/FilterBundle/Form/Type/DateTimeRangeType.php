<?php

namespace Oro\Bundle\FilterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class DateTimeRangeType extends AbstractType
{
    const NAME = 'oro_type_datetime_range';

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
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
        return DateRangeType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'field_type' => 'datetime',
                'field_options' => array(
                    'format' => 'yyyy-MM-dd HH:mm',
                    'view_timezone' => $this->localeSettings->getTimeZone(),
                ),
            )
        );
    }
}
