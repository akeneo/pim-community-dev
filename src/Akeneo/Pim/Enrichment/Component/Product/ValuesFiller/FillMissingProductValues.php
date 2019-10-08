<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

final class FillMissingProductValues
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

        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        $attributesInFamily = $family->getAttributes()->toArray();

        $productValuesInPivotFormat = $this->createProductValuesInPivotFormat($productStandardFormat);
        $nullValuesInPivotFormat = $this->createNullValuesInPivotFormat($attributesInFamily);

        $productValuesWithNullValuesInPivotFormat = array_replace_recursive($nullValuesInPivotFormat, $productValuesInPivotFormat);
        $productStandardFormat['values'] = $this->pivotFormatToStandardFormat($productValuesWithNullValuesInPivotFormat);

        //$productStandardFormat = $this->addPriceValuesInStandardFormat($productStandardFormat);

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
    private function createNullValuesInPivotFormat(array $attributesInFamily): array
    {
        $nullValues = [];
        foreach ($attributesInFamily as $attribute) {
            if (!$attribute->isScopable() && !$attribute->isLocalizable()) {
                $nullValues[$attribute->getCode()]['<all_channels>']['<all_locales>'] = null;
            } else if ($attribute->isScopable() && !$attribute->isLocalizable()) {
                foreach ($this->getChannels() as $channel) {
                    $nullValues[$attribute->getCode()][$channel->getCode()]['<all_locales>'] = null;
                }
            } else if (!$attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->getLocales() as $locale) {
                    $nullValues[$attribute->getCode()]['<all_channels>'][$locale->getCode()] = null;
                }
            } else if ($attribute->isScopable() && $attribute->isLocalizable()) {
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
     *
     * The format is the following:
     * [
     *     'attribute_code_1' => [ 'channel_code' => [ 'locale_code' => 'non_null_data' ]],
     *     'attribute_code_2' => [ '<all_channels>' => [ '<all_locales>' => ['option_code'] ]]
     * ]
     */
    private function createProductValuesInPivotFormat(array $productStandardFormat): array
    {
        $valuesInPivotFormat = [];
        $valuesInStandardFormat = $productStandardFormat['values'];

        foreach ($valuesInStandardFormat as $attributeCode => $values) {
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
                        'scope' => '<all_channels>' === $channelCode ? null : $channelCode,
                        'locale' => '<all_locales>' === $localeCode ? null : $localeCode,
                        'data' => $data,
                    ];
                }
            }
        }

        return $valuesInStandardFormat;
    }

    private function addPriceValuesInStandardFormat(array $productStandardFormat): array
    {
        return [];
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
}
