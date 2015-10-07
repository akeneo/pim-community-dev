<?php

namespace Oro\Bundle\LocaleBundle\Formatter;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfiguration;

class AddressFormatter
{
    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @var NameFormatter
     */
    protected $nameFormatter;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @param LocaleSettings $localeSettings
     * @param NameFormatter $nameFormatter
     */
    public function __construct(LocaleSettings $localeSettings, NameFormatter $nameFormatter)
    {
        $this->localeSettings = $localeSettings;
        $this->nameFormatter = $nameFormatter;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Format address
     *
     * @param AddressInterface $address
     * @param null|string $country
     * @param string $newLineSeparator
     * @return string
     */
    public function format(AddressInterface $address, $country = null, $newLineSeparator = "\n")
    {
        if (!$country) {
            $country = null;
            if ($this->localeSettings->isFormatAddressByAddressCountry()) {
                $country = $address->getCountryIso2();
            } else {
                $country = $this->localeSettings->getCountry();
            }
            if (!$country) {
                $country = LocaleConfiguration::DEFAULT_COUNTRY;
            }
        }

        $format = $this->getAddressFormat($country);
        $countryLocale = $this->localeSettings->getLocaleByCountry($country);
        $formatted = preg_replace_callback(
            '/%(\w+)%/',
            function ($data) use ($address, $countryLocale, $newLineSeparator) {
                $key = $data[1];
                $lowerCaseKey = strtolower($key);
                if ('name' === $lowerCaseKey) {
                    $value = $this->nameFormatter->format($address, $countryLocale);
                } elseif ('street' == $lowerCaseKey) {
                    $value = $this->getValue($address, 'street') . ' ' . $this->getValue($address, 'street2');
                } elseif ('street1' == $lowerCaseKey) {
                    $value = $this->getValue($address, 'street');
                } elseif ('country' == $lowerCaseKey) {
                    $value = $this->getValue($address, 'countryName');
                } else {
                    $value = $this->getValue($address, $lowerCaseKey);
                }
                if ($value) {
                    if ($key !== $lowerCaseKey) {
                        $value = strtoupper($value);
                    }
                    return $value;
                }
                return '';
            },
            $format
        );

        $formatted = str_replace(
            $newLineSeparator . $newLineSeparator,
            $newLineSeparator,
            str_replace('\n', $newLineSeparator, $formatted)
        );
        $formatted = preg_replace('/ +/', ' ', $formatted);
        $formatted = preg_replace('/ +\n/', "\n", $formatted);
        return trim($formatted);
    }

    /**
     * Get address format based on locale or region, if argument is not passed locale from
     * system configuration will be used.
     *
     * @param string|null $localeOrRegion
     * @throws \RuntimeException
     */
    public function getAddressFormat($localeOrRegion = null)
    {
        if (!$localeOrRegion) {
            $localeOrRegion = $this->localeSettings->getLocale();
        }

        $addressFormats = $this->localeSettings->getAddressFormats();

        // matched by country (for example - "RU")
        if (isset($addressFormats[$localeOrRegion][LocaleSettings::ADDRESS_FORMAT_KEY])) {
            return $addressFormats[$localeOrRegion][LocaleSettings::ADDRESS_FORMAT_KEY];
        }

        // matched by locale region - "CA"
        $localeParts = \Locale::parseLocale($localeOrRegion);
        if (isset($localeParts[\Locale::REGION_TAG])) {
            $match = $localeParts[\Locale::REGION_TAG];
            if (isset($match, $addressFormats[$match][LocaleSettings::ADDRESS_FORMAT_KEY])) {
                return $addressFormats[$match][LocaleSettings::ADDRESS_FORMAT_KEY];
            }
        }

        // match by default country in system configuration settings
        $match = $this->localeSettings->getCountry();
        if ($match !== $localeOrRegion && isset($addressFormats[$match][LocaleSettings::ADDRESS_FORMAT_KEY])) {
            return $addressFormats[$match][LocaleSettings::ADDRESS_FORMAT_KEY];
        }

        // fallback to default country
        $match = LocaleConfiguration::DEFAULT_COUNTRY;
        if (isset($addressFormats[$match][LocaleSettings::ADDRESS_FORMAT_KEY])) {
            return $addressFormats[$match][LocaleSettings::ADDRESS_FORMAT_KEY];
        }

        throw new \RuntimeException(sprintf('Cannot get address format for "%s"', $localeOrRegion));
    }

    /**
     * @param object $obj
     * @param string $property
     * @return mixed|null
     */
    protected function getValue($obj, $property)
    {
        try {
            $value = $this->propertyAccessor->getValue($obj, $property);
        } catch (NoSuchPropertyException $e) {
            $value = null;
        }
        return $value;
    }
}
