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
     * @var array
     */
    protected $formatTypesMatch = array(
        'none'   => \IntlDateFormatter::NONE,
        'short'  => \IntlDateFormatter::SHORT,
        'medium' => \IntlDateFormatter::MEDIUM,
        'long'   => \IntlDateFormatter::LONG,
        'full'   => \IntlDateFormatter::FULL,
    );

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
     * @param string|null $locale
     * @param string|null $dateFormat
     * @return string
     */
    public function getDateFormat($type, $locale = null, $dateFormat = null)
    {
        return $this->converterRegistry->getFormatConverter($type)->getDateFormat(
            $locale,
            $this->parseFormatType($dateFormat)
        );
    }

    /**
     * @param string $type
     * @param string|null $locale
     * @param string|null $timeFormat
     * @return string
     */
    public function getTimeFormat($type, $locale = null, $timeFormat = null)
    {
        return $this->converterRegistry->getFormatConverter($type)->getTimeFormat(
            $locale,
            $this->parseFormatType($timeFormat)
        );
    }

    /**
     * @param string $type
     * @param string|null $locale
     * @param string|null $dateFormat
     * @param string|null $timeFormat
     * @return string
     */
    public function getDateTimeFormat($type, $locale = null, $dateFormat = null, $timeFormat = null)
    {
        return $this->converterRegistry->getFormatConverter($type)->getDateTimeFormat(
            $locale,
            $this->parseFormatType($dateFormat),
            $this->parseFormatType($timeFormat)
        );
    }

    /**
     * Convert string representation to Intl constant
     *
     * @param string|null $formatType
     * @return int|null
     */
    protected function parseFormatType($formatType)
    {
        if (isset($this->formatTypesMatch[$formatType])) {
            return $this->formatTypesMatch[$formatType];
        }

        return null;
    }
}
