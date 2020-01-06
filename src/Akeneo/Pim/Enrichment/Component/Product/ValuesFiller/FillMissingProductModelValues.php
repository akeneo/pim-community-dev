<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\CurrencyInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FillMissingProductModelValues implements FillMissingValuesInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $familyVariantRepository;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var ChannelInterface[] */
    private $channels;

    /** @var LocaleInterface[] */
    private $locales;

    public function __construct(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fromStandardFormat(array $productModelStandardFormat): array
    {
        $familyVariantCode = $productModelStandardFormat['family_variant'];
        Assert::notNull($familyVariantCode);
        $level = null === $productModelStandardFormat['parent'] ? 0 : 1;

        $productModelValuesInPivotFormat = $this->createProductModelValuesInPivotFormat($productModelStandardFormat);
        $nullValuesInPivotFormat = $this->createNullValuesInPivotFormat($familyVariantCode, $level);
        $productValuesWithNullValuesInPivotFormat = array_replace_recursive(
            $nullValuesInPivotFormat,
            $productModelValuesInPivotFormat
        );
        $standardProductModelValues = $this->pivotFormatToStandardFormat($productValuesWithNullValuesInPivotFormat);

        $priceProductModelValuesInPivotFormat = $this->createPriceProductModelValuesInPivotFormat($productModelStandardFormat);
        $nullPriceValuesInPivotFormat = $this->createNullPriceValuesInPivotFormat($familyVariantCode, $level);
        $priceProductModelValuesWithNullValuesInPivotFormat = array_replace_recursive(
            $nullPriceValuesInPivotFormat,
            $priceProductModelValuesInPivotFormat
        );
        $standardPriceProductModelValues = $this->pivotFormatToStandardFormatForPriceValues(
            $priceProductModelValuesWithNullValuesInPivotFormat
        );

        $productModelStandardFormat['values'] = $standardProductModelValues + $standardPriceProductModelValues;


        return $productModelStandardFormat;
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
    private function createNullValuesInPivotFormat(string $familyVariantCode, int $level): array
    {
        $nullValues = [];

        $attributesInFamily = $this->getAttributesInFamilyVariantIndexedByCode($familyVariantCode, $level);
        $nonPriceAttributes = array_filter(
            $attributesInFamily,
            function (AttributeInterface $attribute): bool {
                return AttributeTypes::PRICE_COLLECTION !== $attribute->getType();
            }
        );

        foreach ($nonPriceAttributes as $attribute) {
            switch ($attribute->getType()) {
                case AttributeTypes::METRIC:
                    $nullValue = ['unit' => null, 'amount' => null];
                    break;
                case AttributeTypes::OPTION_MULTI_SELECT:
                case AttributeTypes::REFERENCE_DATA_MULTI_SELECT:
                case AttributeTypes::REFERENCE_ENTITY_COLLECTION:
                case AttributeTypes::ASSET_COLLECTION:
                    $nullValue = [];
                    break;
                default:
                    $nullValue = null;
                    break;
            }

            if (!$attribute->isScopable() && !$attribute->isLocalizable()) {
                $nullValues[$attribute->getCode()]['<all_channels>']['<all_locales>'] = $nullValue;
            } elseif ($attribute->isScopable() && !$attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    $nullValues[$attribute->getCode()][$channel->getCode()]['<all_locales>'] = $nullValue;
                }
            } elseif (!$attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getLocales() as $locale) {
                    $nullValues[$attribute->getCode()]['<all_channels>'][$locale->getCode()] = $nullValue;
                }
            } elseif ($attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    foreach ($channel->getLocales() as $locale) {
                        $nullValues[$attribute->getCode()][$channel->getCode()][$locale->getCode()] = $nullValue;
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
    private function createProductModelValuesInPivotFormat(array $productModelStandardFormat): array
    {
        $attributesInFamily = $this->getAttributesInFamilyVariantIndexedByCode(
            $productModelStandardFormat['family_variant'],
            null === $productModelStandardFormat['parent'] ? 0 : 1
        );
        $nonPriceAttributes = array_filter(
            $attributesInFamily,
            function (AttributeInterface $attribute): bool {
                return AttributeTypes::PRICE_COLLECTION !== $attribute->getType();
            }
        );

        $valuesInPivotFormat = [];
        foreach ($productModelStandardFormat['values'] as $attributeCode => $values) {
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
                        'scope' => '<all_channels>' === $channelCode ? null : (string)$channelCode,
                        'locale' => '<all_locales>' === $localeCode ? null : (string)$localeCode,
                        'data' => $data,
                    ];
                }
            }
        }

        return $valuesInStandardFormat;
    }

    /**
     * Create null Price values in a pivot format to ease the use of array replace recursive.
     * The list of prices (in a value) is sorted by currency code, as it is what the front-end expects
     *
     * The format is the following:
     * [
     *     'attribute_code_1' => [ 'channel_code' => [ 'locale_code' => ['USD' => null ]],
     *     'attribute_code_2' => [ '<all_channels>' => [ '<all_locales>' => ['EUR' => null, 'USD' => null ] ]]
     * ]
     */
    private function createNullPriceValuesInPivotFormat(string $familyVariantCode, int $level): array
    {
        $nullValues = [];
        $attributesInFamily = $this->getAttributesInFamilyVariantIndexedByCode($familyVariantCode, $level);

        $priceAttributes = array_filter(
            $attributesInFamily,
            function (AttributeInterface $attribute): bool {
                return AttributeTypes::PRICE_COLLECTION === $attribute->getType();
            }
        );

        foreach ($priceAttributes as $attribute) {
            if (!$attribute->isScopable() && !$attribute->isLocalizable()) {
                foreach ($this->getCurrencies() as $currency) {
                    $nullValues[$attribute->getCode()]['<all_channels>']['<all_locales>'][$currency->getCode()] = null;
                }
            } elseif ($attribute->isScopable() && !$attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    foreach ($this->sortCurrenciesByCode($channel->getCurrencies()->toArray()) as $currency) {
                        $nullValues[$attribute->getCode()][$channel->getCode()]['<all_locales>'][$currency->getCode(
                        )] = null;
                    }
                }
            } elseif (!$attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getLocales() as $locale) {
                    foreach ($this->getCurrencies() as $currency) {
                        $nullValues[$attribute->getCode()]['<all_channels>'][$locale->getCode()][$currency->getCode(
                        )] = null;
                    }
                }
            } elseif ($attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    foreach ($channel->getLocales() as $locale) {
                        foreach ($this->sortCurrenciesByCode($channel->getCurrencies()->toArray()) as $currency) {
                            $nullValues[$attribute->getCode()][$channel->getCode()][$locale->getCode(
                            )][$currency->getCode()] = null;
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
    private function createPriceProductModelValuesInPivotFormat(array $productModelStandardFormat): array
    {
        $attributesInFamily = $this->getAttributesInFamilyVariantIndexedByCode(
            $productModelStandardFormat['family_variant'],
            null === $productModelStandardFormat['parent'] ? 0 : 1
        );
        $priceAttributes = array_filter(
            $attributesInFamily,
            function (AttributeInterface $attribute): bool {
                return AttributeTypes::PRICE_COLLECTION === $attribute->getType();
            }
        );

        $valuesInPivotFormat = [];
        foreach ($productModelStandardFormat['values'] as $attributeCode => $values) {
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
                        $standardFormatData[] = ['currency' => (string)$currencyCode, 'amount' => $amount];
                    }

                    $valuesInStandardFormat[$attributeCode][] = [
                        'scope' => '<all_channels>' === $channelCode ? null : (string)$channelCode,
                        'locale' => '<all_locales>' === $localeCode ? null : (string)$localeCode,
                        'data' => $standardFormatData,
                    ];
                }
            }
        }

        return $valuesInStandardFormat;
    }

    private function getChannels(): array
    {
        if (null === $this->channels) {
            $this->channels = $this->channelRepository->findAll();
        }

        return $this->channels;
    }

    private function getLocales(): array
    {
        if (null === $this->locales) {
            $this->locales = $this->localeRepository->getActivatedLocales();
        }

        return $this->locales;
    }

    private function getCurrencies(): array
    {
        $currencies = [];
        foreach ($this->getChannels() as $channel) {
            foreach ($channel->getCurrencies() as $currency) {
                $currencies[$currency->getCode()] = $currency;
            }
        }

        ksort($currencies);

        return $currencies;
    }

    /**
     * Gets the family attributes for the provided level
     * (0: common attributes, 1: common attributes + variant attribute set 1), indexed by attribute code
     */
    private function getAttributesInFamilyVariantIndexedByCode(string $familyVariantCode, int $level): array
    {
        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariantCode);

        $attributes = [];
        foreach ($familyVariant->getCommonAttributes() as $commonAttribute) {
            $attributes[$commonAttribute->getCode()] = $commonAttribute;
        }
        if (1 === $level) {
            foreach ($familyVariant->getVariantAttributeSet(1)->getAttributes() as $attribute) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }

    private function sortCurrenciesByCode(array $currencies): array
    {
        usort($currencies, function (CurrencyInterface $a, CurrencyInterface $b) {
            return $a->getCode() <=> $b->getCode();
        });

        return $currencies;
    }
}
