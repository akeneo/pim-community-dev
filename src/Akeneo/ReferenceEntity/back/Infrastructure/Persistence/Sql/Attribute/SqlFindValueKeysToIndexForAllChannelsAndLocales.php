<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindValueKeysToIndexForAllChannelsAndLocales implements FindValueKeysToIndexForAllChannelsAndLocalesInterface
{
    private array $cachedResult = [];

    public function __construct(
        private FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        private AttributeRepositoryInterface $attributeRepository,
    ) {
    }

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        if (!isset($this->cachedResult[$referenceEntityIdentifier->normalize()])) {
            $this->cachedResult[$referenceEntityIdentifier->normalize()] = $this->generateSearchMatrixWithValueKeys($referenceEntityIdentifier);
        }

        return $this->cachedResult[$referenceEntityIdentifier->normalize()];
    }

    private function generateSearchMatrixWithValueKeys(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $matrixLocalesPerChannels = $this->findActivatedLocalesPerChannels->findAll();
        $attributes = $this->attributeRepository->findByReferenceEntity($referenceEntityIdentifier);
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
