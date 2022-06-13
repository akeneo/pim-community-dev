<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindValueKeysToIndexForAllChannelsAndLocales implements FindValueKeysToIndexForAllChannelsAndLocalesInterface
{
    /** @var array<string, array> */
    private array $cachedResult = [];

    public function __construct(
        private FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        private AttributeRepositoryInterface $attributeRepository,
    ) {
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        if (!isset($this->cachedResult[$assetFamilyIdentifier->normalize()])) {
            $this->cachedResult[$assetFamilyIdentifier->normalize()] = $this->generateSearchMatrixWithValueKeys($assetFamilyIdentifier);
        }

        return $this->cachedResult[$assetFamilyIdentifier->normalize()];
    }

    private function generateSearchMatrixWithValueKeys(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $matrixLocalesPerChannels = $this->findActivatedLocalesPerChannels->findAll();
        $attributes = $this->attributeRepository->findByAssetFamily($assetFamilyIdentifier);
        $textAttributes = array_filter($attributes, function (AbstractAttribute $attribute) {
            return $attribute->getType() === 'text';
        });

        $matrix = [];
        foreach ($matrixLocalesPerChannels as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $valueKeys = $this->generateValueKeys(
                    $textAttributes,
                    ChannelIdentifier::fromCode($channelCode),
                    LocaleIdentifier::fromCode($localeCode)
                );
                $matrix[$channelCode][$localeCode] = $valueKeys;
            }
        }

        return $matrix;
    }

    /**
     * @param AbstractAttribute[] $attributes
     *
     * @return string[]
     */
    private function generateValueKeys(
        array $attributes,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifier $localeIdentifier
    ): array {
        $valueKeys = [];

        foreach ($attributes as $attribute) {
            $scopable = $attribute->hasValuePerChannel();
            $localizable = $attribute->hasValuePerLocale();

            if ($scopable && $localizable) {
                $valueKeys[] = sprintf('%s_%s_%s', $attribute->getIdentifier()->stringValue(), $channelIdentifier->normalize(), $localeIdentifier->normalize());
            } elseif ($scopable && !$localizable) {
                $valueKeys[] = sprintf('%s_%s', $attribute->getIdentifier()->stringValue(), $channelIdentifier->normalize());
            } elseif (!$scopable && $localizable) {
                $valueKeys[] = sprintf('%s_%s', $attribute->getIdentifier()->stringValue(), $localeIdentifier->normalize());
            } else {
                $valueKeys[] = $attribute->getIdentifier()->stringValue();
            }
        }

        return $valueKeys;
    }
}
