<?php

namespace Oro\Bundle\LocaleBundle\Formatter;

use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\LocaleBundle\Model\MiddleNameInterface;
use Oro\Bundle\LocaleBundle\Model\NamePrefixInterface;
use Oro\Bundle\LocaleBundle\Model\NameSuffixInterface;
use Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider;

class NameFormatter
{
    /**
     * @var LocaleSettingsProvider
     */
    protected $settingsProvider;

    public function __construct(LocaleSettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * @param object $person
     * @param null|string $locale
     * @return string
     */
    public function format($person, $locale = null)
    {
        $nameParts = array();
        if ($person instanceof NamePrefixInterface) {
            $nameParts['prefix'] = $person->getNamePrefix();
        }
        if ($person instanceof FirstNameInterface) {
            $nameParts['first_name'] = $person->getFirstName();
        }
        if ($person instanceof MiddleNameInterface) {
            $nameParts['middle_name'] = $person->getMiddleName();
        }
        if ($person instanceof LastNameInterface) {
            $nameParts['last_name'] = $person->getLastName();
        }
        if ($person instanceof NameSuffixInterface) {
            $nameParts['suffix'] = $person->getNameSuffix();
        }

        $format = $this->settingsProvider->getNameFormat($locale);
        $name = preg_replace_callback(
            '/%(\w+)%/',
            function ($data) use ($nameParts) {
                $key = $data[1];
                $lowerCaseKey = strtolower($key);
                $hasData = isset($nameParts[$lowerCaseKey]);
                if ($hasData && $key !== $lowerCaseKey) {
                    $nameParts[$lowerCaseKey] = strtoupper($nameParts[$lowerCaseKey]);
                }
                return $hasData ? $nameParts[$lowerCaseKey] : '';
            },
            $format
        );

        return trim($name);
    }
}
