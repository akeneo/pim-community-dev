<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Doctrine\DBAL\Connection;

/**
 * Find value keys for a given list of attribute types
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindValueKeysByAttributeType implements FindValueKeysByAttributeTypeInterface
{
    /** @var array<string, string> */
    private array $cachedResult = [];

    public function __construct(
        private Connection $sqlConnection,
        private FindLocales $findLocales,
        private FindChannels $findChannels,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $attributeTypes): array
    {
        $cacheKey = $this->getCacheKey($assetFamilyIdentifier, $attributeTypes);
        if (!isset($this->cachedResult[$cacheKey])) {
            $this->cachedResult[$cacheKey] = $this->fetch($assetFamilyIdentifier, $attributeTypes);
        }

        return $this->cachedResult[$cacheKey];
    }

    private function fetch(AssetFamilyIdentifier $assetFamilyIdentifier, array $attributeTypes): array
    {
        $attributes = $this->findAttributesByFamilyIdentifierAndTypes($assetFamilyIdentifier, $attributeTypes);
        $locales = $this->findLocales->findAllActivated();
        $channels = $this->findChannels->findAll();
        $valueKeysByAttributeType = [];

        foreach($attributes as $attribute) {
            if ('1' === $attribute['value_per_channel']
                && '1' === $attribute['value_per_locale']
            ) {
                $valueKeysByAttributeType[] = $this->generateLocalisabeAndScopableValueKeys($attribute['identifier'], $channels);
            } elseif (
                '1' === $attribute['value_per_channel']
                && '0' === $attribute['value_per_locale']
            ) {
                $valueKeysByAttributeType[] = $this->generateScopableValueKeys($attribute['identifier'], $channels);
            } elseif (
                '0' === $attribute['value_per_channel']
                && '1' === $attribute['value_per_locale']
            ) {
                $valueKeysByAttributeType[] = $this->generateLocalisableValueKeys($attribute['identifier'], $locales);
            } else {
                $valueKeysByAttributeType[] = [$attribute['identifier']];
            }
        }

        return array_merge(...$valueKeysByAttributeType);
    }

    private function findAttributesByFamilyIdentifierAndTypes(AssetFamilyIdentifier $assetFamilyIdentifier, array $attributeTypes): array
    {
        $query = <<<SQL
            SELECT
                attribute.identifier,
                attribute.value_per_channel,
                attribute.value_per_locale
            FROM akeneo_asset_manager_attribute as attribute
            WHERE attribute.asset_family_identifier = :asset_family_identifier
            AND attribute.attribute_type IN (:types)
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'asset_family_identifier' => $assetFamilyIdentifier,
                'types' => $attributeTypes,
            ],
            [
                'types' => Connection::PARAM_STR_ARRAY
            ]
        );

        return $statement->fetchAllAssociative();
    }

    /**
     * @param $channels Channel[];
     */
    private function generateLocalisabeAndScopableValueKeys(string $attributeIdentifier, array $channels): array
    {
        $valueKeys = [];

        foreach($channels as $channel) {
            foreach($channel->getLocaleCodes() as $localeCode) {
                $valueKeys[] = $attributeIdentifier . '_' . $channel->getCode() . '_' . $localeCode;
            }
        }

        return $valueKeys;
    }

    /**
     * @param $channels Channel[];
     */
    private function generateScopableValueKeys(string $attributeIdentifier, array $channels): array
    {
        return array_map(
            static fn (Channel $channel) => $attributeIdentifier . '_' . $channel->getCode(),
            $channels
        );
    }

    /**
     * @param $locales Locale[];
     */
    private function generateLocalisableValueKeys(string $attributeIdentifier, array $locales): array
    {
        return array_map(
            static fn (Locale $locale) => $attributeIdentifier . '_' . $locale->getCode(),
            $locales
        );
    }

    private function getCacheKey(AssetFamilyIdentifier $assetFamilyIdentifier, array $attributeTypes): string
    {
        return sprintf(
            '%s_%s',
            (string) $assetFamilyIdentifier,
            implode('_', $attributeTypes)
        );
    }
}
