<?php

namespace Oro\Bundle\LocaleBundle\Formatter;

use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\LocaleBundle\Model\MiddleNameInterface;
use Oro\Bundle\LocaleBundle\Model\NamePrefixInterface;
use Oro\Bundle\LocaleBundle\Model\NameSuffixInterface;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class NameFormatter
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param NamePrefixInterface|FirstNameInterface|MiddleNameInterface|LastNameInterface|NameSuffixInterface $person
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

        $format = $this->localeSettings->getNameFormat($locale);
        $name = preg_replace_callback(
            '/%(\w+)%/',
            function ($data) use ($nameParts) {
                $key = $data[1];
                $lowerCaseKey = strtolower($key);
                if (isset($nameParts[$lowerCaseKey])) {
                    if ($key !== $lowerCaseKey) {
                        $nameParts[$lowerCaseKey] = strtoupper($nameParts[$lowerCaseKey]);
                    }
                    return $nameParts[$lowerCaseKey];
                }
                return '';
            },
            $format
        );

        return trim($name);
    }
}
