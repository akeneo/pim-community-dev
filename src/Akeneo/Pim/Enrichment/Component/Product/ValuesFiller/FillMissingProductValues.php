<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * The standard format of a product does not contain values that are not filled.
 * We need to have even values that are not filled for rendering purpose, such as in the PEF or the export.
 *
 * The goal of this class is to generate all missing values of a product, including values that are missing in parent product models.
 * It uses an internal pivot format to ease the merge. The price is handled in dedicated function to isolate the behavior of this attribute type.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FillMissingProductValues
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var ChannelInterface[] */
    private $channels;

    /** @var LocaleInterface[] */
    private $locales;

    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository)
    {
        $this->familyRepository = $familyRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fromStandardFormat(array $productStandardFormat): array
    {
        $familyCode = $productStandardFormat['family'];
        if (null === $familyCode) {
            return $productStandardFormat;
        }

        $productValuesInPivotFormat = $this->createProductValuesInPivotFormat($productStandardFormat);
        $nullValuesInPivotFormat = $this->createNullValuesInPivotFormat($familyCode);
        $productValuesWithNullValuesInPivotFormat = array_replace_recursive($nullValuesInPivotFormat, $productValuesInPivotFormat);
        $standardProductValues = $this->pivotFormatToStandardFormat($productValuesWithNullValuesInPivotFormat);

        $priceProductValuesInPivotFormat = $this->createPriceProductValuesInPivotFormat($productStandardFormat);
        $nullPriceValuesInPivotFormat = $this->createNullPriceValuesInPivotFormat($familyCode);
        $priceProductValuesWithNullValuesInPivotFormat = array_replace_recursive($nullPriceValuesInPivotFormat, $priceProductValuesInPivotFormat);
        $standardPriceProductValues = $this->pivotFormatToStandardFormatForPriceValues($priceProductValuesWithNullValuesInPivotFormat);

        $productStandardFormat['values'] = array_merge($standardProductValues, $standardPriceProductValues);

        return $productStandardFormat;
    }

    /**
     * Create null values in a pivot format to ease the use of array replace recursive.
     *
     * The format is the following:
     * [
     *     'attribute_code_1' => [ 'channel_code' => [ 'locale_code' => null ]],
     *     'attribute_code_2' => [ '<all_channels>' => [ '<all_locales>' => null ]]
     * ]
     */
    private function createNullValuesInPivotFormat(string $familyCode): array
    {
        $nullValues = [];

        $attributesInFamily = $this->getAttributesInFamilyIndexedByCode($familyCode);
        $nonPriceAttributes = array_filter($attributesInFamily, function (AttributeInterface $attribute): bool {
            return AttributeTypes::PRICE_COLLECTION !== $attribute->getType();
        });

        foreach ($nonPriceAttributes as $attribute) {
            if (!$attribute->isScopable() && !$attribute->isLocalizable()) {
                $nullValues[$attribute->getCode()]['<all_channels>']['<all_locales>'] = null;
            } elseif ($attribute->isScopable() && !$attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    $nullValues[$attribute->getCode()][$channel->getCode()]['<all_locales>'] = null;
                }
            } elseif (!$attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getLocales() as $locale) {
                    $nullValues[$attribute->getCode()]['<all_channels>'][$locale->getCode()] = null;
                }
            } elseif ($attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    foreach ($channel->getLocales() as $locale) {
                        $nullValues[$attribute->getCode()][$channel->getCode()][$locale->getCode()] = null;
                    }
                }
            }
        }

        return $nullValues;
    }

    /**
     * Create existing values in a pivot format to ease the use of array replace recursive.
     * It does no include price values because this attribute type is the only one that has a different behavior.
     *
     * The format is the following:
     * [
     *     'attribute_code_1' => [ 'channel_code' => [ 'locale_code' => 'non_null_data' ]],
     *     'attribute_code_2' => [ '<all_channels>' => [ '<all_locales>' => ['option_code'] ]]
     * ]
     */
    private function createProductValuesInPivotFormat(array $productStandardFormat): array
    {
        $attributesInFamily = $this->getAttributesInFamilyIndexedByCode($productStandardFormat['family']);
        $nonPriceAttributes = array_filter($attributesInFamily, function (AttributeInterface $attribute): bool {
            return AttributeTypes::PRICE_COLLECTION !== $attribute->getType();
        });

        $valuesInPivotFormat = [];
        foreach ($productStandardFormat['values'] as $attributeCode => $values) {
            if (!isset($nonPriceAttributes[$attributeCode])) {
                continue;
            }
            foreach ($values as $value) {
                $channelCode = null === $value['scope'] ? '<all_channels>' : $value['scope'];
                $localeCode = null === $value['locale'] ? '<all_locales>' : $value['locale'];
                $valuesInPivotFormat[$attributeCode][$channelCode][$localeCode] = $value['data'];
            }
        }

        return $valuesInPivotFormat;
    }

    private function pivotFormatToStandardFormat(array $valuesInPivotFormat): array
    {
        $valuesInStandardFormat = [];
        foreach ($valuesInPivotFormat as $attributeCode => $valuesIndexedByChannel) {
            foreach ($valuesIndexedByChannel as $channelCode => $valuesIndexedByLocale) {
                foreach ($valuesIndexedByLocale as $localeCode => $data) {
                    $valuesInStandardFormat[$attributeCode][] = [
                        'scope' => '<all_channels>' === $channelCode ? null : (string) $channelCode,
                        'locale' => '<all_locales>' === $localeCode ? null : (string) $localeCode,
                        'data' => $data,
                    ];
                }
            }
        }

        return $valuesInStandardFormat;
    }

    /**
     * Create null Price values in a pivot format to ease the use of array replace recursive.
     *
     * The format is the following:
     * [
     *     'attribute_code_1' => [ 'channel_code' => [ 'locale_code' => ['USD' => null ]],
     *     'attribute_code_2' => [ '<all_channels>' => [ '<all_locales>' => ['EUR' => null, 'USD' => null ] ]]
     * ]
     */
    private function createNullPriceValuesInPivotFormat(string $familyCode): array
    {
        $nullValues = [];
        $attributesInFamily = $this->getAttributesInFamilyIndexedByCode($familyCode);

        $priceAttributes = array_filter($attributesInFamily, function (AttributeInterface $attribute): bool {
            return AttributeTypes::PRICE_COLLECTION === $attribute->getType();
        });

        foreach ($priceAttributes as $attribute) {
            if (!$attribute->isScopable() && !$attribute->isLocalizable()) {
                foreach ($this->getCurrencies() as $currency) {
                    $nullValues[$attribute->getCode()]['<all_channels>']['<all_locales>'][$currency->getCode()] = null;
                }
            } elseif ($attribute->isScopable() && !$attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    foreach ($channel->getCurrencies() as $currency) {
                        $nullValues[$attribute->getCode()][$channel->getCode()]['<all_locales>'][$currency->getCode()] = null;
                    }
                }
            } elseif (!$attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getLocales() as $locale) {
                    foreach ($this->getCurrencies() as $currency) {
                        $nullValues[$attribute->getCode()]['<all_channels>'][$locale->getCode()][$currency->getCode()] = null;
                    }
                }
            } elseif ($attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    foreach ($channel->getLocales() as $locale) {
                        foreach ($channel->getCurrencies() as $currency) {
                            $nullValues[$attribute->getCode()][$channel->getCode()][$locale->getCode()][$currency->getCode()] = null;
                        }
                    }
                }
            }
        }

        return $nullValues;
    }

    /**
     * Create existing price values in a pivot format to ease the use of array replace recursive.
     * It only handles price values because this attribute type is the only one that has a different behavior.
     *
     * The format is the following:
     * [
     *     'attribute_code_1' => [ 'channel_code' => [ 'locale_code' => '['USD' => '10.00' ] ]],
     *     'attribute_code_2' => [ '<all_channels>' => [ '<all_locales>' => ['EUR' => '12.00', 'USD' => '14.00' ] ]]
     * ]
     */
    private function createPriceProductValuesInPivotFormat(array $productStandardFormat): array
    {
        $attributesInFamily = $this->getAttributesInFamilyIndexedByCode($productStandardFormat['family']);
        $priceAttributes = array_filter($attributesInFamily, function (AttributeInterface $attribute): bool {
            return AttributeTypes::PRICE_COLLECTION === $attribute->getType();
        });

        $valuesInPivotFormat = [];
        foreach ($productStandardFormat['values'] as $attributeCode => $values) {
            if (!isset($priceAttributes[$attributeCode])) {
                continue;
            }
            foreach ($values as $value) {
                $channelCode = null === $value['scope'] ? '<all_channels>' : $value['scope'];
                $localeCode = null === $value['locale'] ? '<all_locales>' : $value['locale'];

                foreach ($value['data'] as $price) {
                    $valuesInPivotFormat[$attributeCode][$channelCode][$localeCode][$price['currency']] = $price['amount'];
                }
            }
        }

        return $valuesInPivotFormat;
    }

    private function pivotFormatToStandardFormatForPriceValues(array $valuesInPivotFormat): array
    {
        $valuesInStandardFormat = [];
        foreach ($valuesInPivotFormat as $attributeCode => $valuesIndexedByChannel) {
            foreach ($valuesIndexedByChannel as $channelCode => $valuesIndexedByLocale) {
                foreach ($valuesIndexedByLocale as $localeCode => $valuesByCurrency) {
                    $standardFormatData = [];

                    foreach ($valuesByCurrency as $currencyCode => $amount) {
                        $standardFormatData[] = ['currency' => (string) $currencyCode, 'amount' => $amount];
                    }

                    $valuesInStandardFormat[$attributeCode][] = [
                        'scope' => '<all_channels>' === $channelCode ? null : (string) $channelCode,
                        'locale' => '<all_locales>' === $localeCode ? null : (string) $localeCode,
                        'data' => $standardFormatData,
                    ];
                }
            }
        }

        return $valuesInStandardFormat;
    }

    private function getChannels() : array
    {
        if (null === $this->channels) {
            $this->channels = $this->channelRepository->findAll();
        }

        return $this->channels;
    }

    private function getLocales() : array
    {
        if (null === $this->locales) {
            $this->locales = $this->localeRepository->getActivatedLocales();
        }

        return $this->locales;
    }

    private function getCurrencies() : array
    {
        $currencies = [];
        foreach ($this->getChannels() as $channel) {
            foreach ($channel->getCurrencies() as $currency) {
                $currencies[$currency->getCode()] = $currency;
            }
        }

        return $currencies;
    }

    private function getAttributesInFamilyIndexedByCode(string $familyCode): array
    {
        $attributesInFamilyIndexedByCode = [];

        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        $attributesInFamily = $family->getAttributes()->toArray();

        foreach ($attributesInFamily as $attribute) {
            $attributesInFamilyIndexedByCode[$attribute->getCode()] = $attribute;
        }

        return $attributesInFamilyIndexedByCode;
    }
}
