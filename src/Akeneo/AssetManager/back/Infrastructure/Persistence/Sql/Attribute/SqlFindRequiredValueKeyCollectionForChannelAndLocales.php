<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindRequiredValueKeyCollectionForChannelAndLocales implements FindRequiredValueKeyCollectionForChannelAndLocalesInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private FindChannels $findChannels,
    ) {
    }

    public function find(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifierCollection $localeIdentifierCollection
    ): ValueKeyCollection {
        Assert::false($localeIdentifierCollection->isEmpty(), 'The list of locales should not be empty.');

        $attributes = $this->findRequiredAttributes($assetFamilyIdentifier);
        $channel = $this->getChannel($channelIdentifier);
        $channelLocaleCodes = array_map(fn (string $localeCode) => strtolower($localeCode), $channel->getLocaleCodes());

        $localeIdentifiers = array_filter(
            $localeIdentifierCollection->normalize(),
            static fn (string $localeIdentifier) => in_array(strtolower($localeIdentifier), $channelLocaleCodes)
        );

        $valueKeys = $this->generateValueKeys($attributes, $channel, $localeIdentifiers);

        return ValueKeyCollection::fromValueKeys($valueKeys);
    }

    private function getChannel(ChannelIdentifier $channelIdentifier): Channel
    {
        $channels = $this->findChannels->findAll();

        foreach ($channels as $channel) {
            if (strtolower($channel->getCode()) === strtolower($channelIdentifier->normalize())) {
                return $channel;
            }
        }

        throw new \Exception(sprintf('Channel with code "%s" can\'t be found', $channelIdentifier->normalize()));
    }

    private function findRequiredAttributes(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $query = <<<SQL
            SELECT 
                attribute.identifier,
                attribute.value_per_channel,
                attribute.value_per_locale
            FROM akeneo_asset_manager_attribute as attribute
            WHERE asset_family_identifier = :asset_family_identifier
            AND is_required = 1 
        SQL;

        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'asset_family_identifier' => $assetFamilyIdentifier->normalize(),
            ],
        );

        return $statement->fetchAllAssociative();
    }

    /**
     * @param string[] $localeCodes
     *
     * @return ValueKey[]
     */
    private function generateScopableAndLocalizableValueKeys(string $attributeIdentifier, Channel $channel, array $localeCodes): array
    {
        $valueKeys = [];

        foreach ($localeCodes as $localeCode) {
            $valueKeys[] = ValueKey::createFromNormalized(
                sprintf('%s_%s_%s', $attributeIdentifier, $channel->getCode(), $localeCode)
            );
        }

        return $valueKeys;
    }

    /**
     * @param string[] $localeCodes
     *
     * @return ValueKey[]
     */
    private function generateLocalizableValueKeys(string $attributeIdentifier, array $localeCodes): array
    {
        $valueKeys = [];

        foreach ($localeCodes as $localeCode) {
            $valueKeys[] = ValueKey::createFromNormalized(
                sprintf('%s_%s', $attributeIdentifier, $localeCode)
            );
        }

        return $valueKeys;
    }

    /**
     * @param string[] $localeIdentifiers
     *
     * @return ValueKey[]
     */
    private function generateValueKeys(array $attributes, Channel $channel, array $localeIdentifiers): array
    {
        $valueKeys = [];

        foreach ($attributes as $attribute) {
            $scopable = '1' === $attribute['value_per_channel'];
            $localizable = '1' === $attribute['value_per_locale'];
            $generatedValueKeys = [];

            if ($scopable && $localizable) {
                $generatedValueKeys = $this->generateScopableAndLocalizableValueKeys(
                    $attribute['identifier'],
                    $channel,
                    $localeIdentifiers,
                );
            } elseif ($scopable && !$localizable) {
                $generatedValueKeys[] = ValueKey::createFromNormalized(
                    sprintf('%s_%s', $attribute['identifier'], $channel->getCode())
                );
            } elseif (!$scopable && $localizable) {
                $generatedValueKeys = $this->generateLocalizableValueKeys(
                    $attribute['identifier'],
                    $localeIdentifiers
                );
            } else {
                $generatedValueKeys[] = ValueKey::createFromNormalized(
                    $attribute['identifier']
                );
            }

            $valueKeys = array_merge($valueKeys, $generatedValueKeys);
        }

        return $valueKeys;
    }
}
