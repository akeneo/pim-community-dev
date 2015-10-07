<?php

namespace Oro\Bundle\LocaleBundle\Twig;

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
            new \Twig_SimpleFunction('oro_datetime_formatter_list', array($this, 'getDateTimeFormatterList')),
            new \Twig_SimpleFunction('oro_date_format', array($this, 'getDateFormat')),
            new \Twig_SimpleFunction('oro_time_format', array($this, 'getTimeFormat')),
            new \Twig_SimpleFunction('oro_datetime_format', array($this, 'getDateTimeFormat')),
        );
    }

    /**
     * @return array
     */
    public function getDateTimeFormatterList()
    {
        return array_keys($this->converterRegistry->getFormatConverters());
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
