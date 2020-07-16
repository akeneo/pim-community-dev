<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Symfony\Component\Intl\Intl;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FlatFileHeader
{
    /** @var string */
    private $code;

    /** @var bool */
    private $isScopable;

    /** @var string */
    private $channelCode;

    /** @var bool */
    private $isLocalizable;

    /** @var array */
    private $localeCodes;

    /** @var bool */
    private $isMedia;

    /** @var bool */
    private $usesUnit;

    /** @var bool */
    private $usesCurrencies;

    /** @var array */
    private $channelCurrencyCodes;

    /** @var array */
    private $allCurrencyCodes;

    /** @var bool */
    private $isLocaleSpecific;

    /** @var array */
    private $specificToLocales;

    private $attributeLabels;
    /**
     * @var string|null
     */
    private $unitLabel;

    public function __construct(
        string $code,
        ?bool $isScopable = false,
        ?string $channelCode = null,
        ?bool $isLocalizable = false,
        ?array $localeCodes = [],
        ?bool $isMedia = false,
        ?bool $usesUnit = false,
        ?bool $usesCurrencies = false,
        ?array $channelCurrencyCodes = [],
        ?array $allCurrencyCodes = [],
        ?bool $isLocaleSpecific = false,
        ?array $specificToLocales = [],
        ?array $attributeLabels = [],
        ?string $unitLabel = ''
    ) {
        if ($isLocaleSpecific && empty($specificToLocales)) {
            throw new \InvalidArgumentException(
                'A list of locales to which the header is specific to must be provided '.
                'when the header is defined as locale specific'
            );
        }

        if ($usesCurrencies && $usesUnit) {
            throw new \InvalidArgumentException(
                'A header cannot have both currencies and unit.'
            );
        }

        $this->code = $code;

        $this->isScopable = $isScopable;
        $this->channelCode = $channelCode;

        $this->isLocalizable = $isLocalizable;
        $this->localeCodes = $localeCodes;

        $this->isMedia = $isMedia;
        $this->usesUnit = $usesUnit;

        $this->usesCurrencies = $usesCurrencies;
        $this->channelCurrencyCodes = $channelCurrencyCodes;
        $this->allCurrencyCodes = $allCurrencyCodes;
        $this->attributeLabels = $attributeLabels;

        $this->isLocaleSpecific = $isLocaleSpecific;
        $this->specificToLocales = $specificToLocales;
        $this->unitLabel = $unitLabel;
    }

    /**
     * Build a FlatFileHeader from product attribute
     */
    public static function buildFromAttributeData(
        string $attributeCode,
        string $attributeType,
        bool $scopable,
        string $channelCode,
        bool $localizable,
        array $localeCodes,
        array $channelCurrencyCodes,
        array $activatedCurrencyCodes,
        array $specificToLocales,
        array $attributeLabels,
        string $unitLabel
    ): FlatFileHeader {
        $mediaAttributeTypes = [
            AttributeTypes::IMAGE,
            AttributeTypes::FILE
        ];

        return new FlatFileHeader(
            $attributeCode,
            $scopable,
            $channelCode,
            $localizable,
            $localeCodes,
            (in_array($attributeType, $mediaAttributeTypes)),
            (AttributeTypes::METRIC === $attributeType),
            (AttributeTypes::PRICE_COLLECTION === $attributeType),
            $channelCurrencyCodes,
            $activatedCurrencyCodes,
            !empty($specificToLocales),
            $specificToLocales,
            $attributeLabels,
            $unitLabel
        );
    }

    /**
     * Indicate whether the header is associated to a media information
     */
    public function isMedia(): bool
    {
        return $this->isMedia;
    }

    /**
     * Generate headers string contextualized on channel
     */
    public function generateHeaderStrings(): array
    {
        if ($this->isLocaleSpecific && count(array_intersect($this->localeCodes, $this->specificToLocales)) === 0) {
            return [];
        }

        $prefixes = [];
        $codeLabel = $this->attributeLabels['fr_FR'] ?? "[$this->code]";

        if ($this->isLocalizable && $this->isScopable) {
            foreach ($this->localeCodes as $localeCode) {
                if (!$this->isLocaleSpecific ||
                    ($this->isLocaleSpecific && in_array($localeCode, $this->specificToLocales))) {

                    $prefixes[] = sprintf('%s (%s, %s)', $codeLabel, $localeCode, $this->channelCode);
                }
            }
        } elseif ($this->isLocalizable) {
            foreach ($this->localeCodes as $localeCode) {
                if (!$this->isLocaleSpecific ||
                    ($this->isLocaleSpecific && in_array($localeCode, $this->specificToLocales))) {
                    $prefixes[] = sprintf('%s (%s)', $codeLabel, $localeCode);
                }
            }
        } elseif ($this->isScopable) {
            $prefixes[] = sprintf('%s (%s)', $codeLabel, $this->channelCode);
        } else {
            $prefixes[] = $codeLabel;
        }

        $headers = [];

        if ($this->usesCurrencies) {
            foreach ($prefixes as $prefix) {
                if ($this->isScopable) {
                    $currencyCodesToUse = $this->channelCurrencyCodes;
                } else {
                    $currencyCodesToUse = $this->allCurrencyCodes;
                }
                foreach ($currencyCodesToUse as $currencyCode) {
                    $language = \Locale::getPrimaryLanguage('fr_FR');
                    $currency = Intl::getCurrencyBundle()->getCurrencyName($currencyCode, $language);

                    $headers[] = sprintf('%s %s', $prefix, $currency);
                }
            }
        } elseif ($this->usesUnit) {
            foreach ($prefixes as $prefix) {
                $headers[] = $prefix;
                $headers[] = sprintf('%s (%s)', $prefix, $this->unitLabel);
            }
        } else {
            $headers = $prefixes;
        }

        return $headers;
    }
}
