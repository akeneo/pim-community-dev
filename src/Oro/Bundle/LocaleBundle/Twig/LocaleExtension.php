<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class LocaleExtension extends \Twig_Extension
{
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_locale', [$this->localeSettings, 'getLocale']),
            new \Twig_SimpleFunction('oro_language', [$this->localeSettings, 'getLanguage']),
            new \Twig_SimpleFunction('oro_country', [$this->localeSettings, 'getCountry']),
            new \Twig_SimpleFunction('oro_currency', [$this->localeSettings, 'getCurrency']),
            new \Twig_SimpleFunction('oro_timezone', [$this->localeSettings, 'getTimeZone']),
            new \Twig_SimpleFunction('oro_timezone_offset', [$this, 'getTimeZoneOffset']),
            new \Twig_SimpleFunction(
                'oro_format_address_by_address_country',
                [$this->localeSettings, 'isFormatAddressByAddressCountry']
            ),
        ];
    }

    /**
     * @return string
     */
    public function getTimeZoneOffset()
    {
        $date = new \DateTime('now', new \DateTimeZone($this->localeSettings->getTimeZone()));
        return $date->format('P');
    }
}
