<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterRegistry;

class DateFormatExtension extends \Twig_Extension
{
    /**
     * @var DateTimeFormatConverterRegistry
     */
    protected $converterRegistry;

    /**
     * @param DateTimeFormatConverterRegistry $converterRegistry
     */
    public function __construct(DateTimeFormatConverterRegistry $converterRegistry)
    {
        $this->converterRegistry = $converterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale_dateformat';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('oro_dateformat', array($this, 'getDateFormat')),
            new \Twig_SimpleFunction('oro_timeformat', array($this, 'getTimeFormat')),
            new \Twig_SimpleFunction('oro_datetimeformat', array($this, 'getDateTimeFormat')),
        );
    }

    /**
     * @param string $type
     * @param string|null $dateType
     * @param string|null $locale
     * @return string
     */
    public function getDateFormat($type, $dateType = null, $locale = null)
    {
        return $this->converterRegistry->getFormatConverter($type)->getDateFormat(
            $dateType,
            $locale
        );
    }

    /**
     * @param string $type
     * @param string|null $timeType
     * @param string|null $locale
     * @return string
     */
    public function getTimeFormat($type, $timeType = null, $locale = null)
    {
        return $this->converterRegistry->getFormatConverter($type)->getTimeFormat(
            $timeType,
            $locale
        );
    }

    /**
     * @param string $type
     * @param string|null $dateType
     * @param string|null $timeType
     * @param string|null $locale
     * @return string
     */
    public function getDateTimeFormat($type, $dateType = null, $timeType = null, $locale = null)
    {
        return $this->converterRegistry->getFormatConverter($type)->getDateTimeFormat(
            $dateType,
            $timeType,
            $locale
        );
    }
}
