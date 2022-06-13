<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\ValueKey;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\ValueKey\GetValueKeyForAttributeChannelAndLocaleInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\FindLocales;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlGetValueKeyForAttributeChannelAndLocale implements GetValueKeyForAttributeChannelAndLocaleInterface
{
    public function __construct(
        private FindChannels $findChannels,
        private FindLocales $findLocales,
        private AttributeRepositoryInterface $attributeRepository
    ) {
    }

    public function fetch(
        AttributeIdentifier $attributeIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifier $localeIdentifier
    ): ValueKey {
        $attribute = null;

        try {
            $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        } catch (AttributeNotFoundException $e) {
            $this->throwException($attributeIdentifier, $channelIdentifier, $localeIdentifier);
        }

        $localizable = $attribute->hasValuePerLocale();
        $scopable = $attribute->hasValuePerChannel();

        $locale = $this->findLocales->find($localeIdentifier->normalize());
        $channel = current(
            array_filter(
                $this->findChannels->findAll(),
                static fn (Channel $channel) => strtolower($channel->getCode()) === strtolower($channelIdentifier->normalize())
            )
        );

        if (($scopable && null === $channel)
            || ($localizable && null === $locale)
            || ($scopable && $localizable && !in_array($locale->getCode(), $channel->getLocaleCodes()))
        ) {
            $this->throwException($attributeIdentifier, $channelIdentifier, $localeIdentifier);
        }

        if ($scopable && $localizable) {
            $valueKey = sprintf('%s_%s_%s', $attributeIdentifier, $channel->getCode(), $locale->getCode());
        } elseif ($scopable && !$localizable) {
            $valueKey = sprintf('%s_%s', $attributeIdentifier, $channel->getCode());
        } elseif (!$scopable && $localizable) {
            $valueKey = sprintf('%s_%s', $attributeIdentifier, $locale->getCode());
        } else {
            $valueKey = $attributeIdentifier->normalize();
        }

        return ValueKey::createFromNormalized($valueKey);
    }

    private function throwException(
        AttributeIdentifier $attributeIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifier $localeIdentifier
    ): void {
        throw new \LogicException(
            sprintf(
                'Expected to find a value key for attribute "%s", channel "%s" and locale "%s"',
                $attributeIdentifier->normalize(),
                $channelIdentifier->normalize(),
                $localeIdentifier->normalize()
            )
        );
    }
}
