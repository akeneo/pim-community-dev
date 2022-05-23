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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
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
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $attributeTypes): array
    {
        $cacheKey = $this->getCacheKey($referenceEntityIdentifier, $attributeTypes);
        if (!isset($this->cachedResult[$cacheKey])) {
            $this->cachedResult[$cacheKey] = $this->fetch($referenceEntityIdentifier, $attributeTypes);
        }

        return $this->cachedResult[$cacheKey];
    }

    private function fetch(ReferenceEntityIdentifier $referenceEntityIdentifier, array $attributeTypes): array
    {
        $attributes = $this->findAttributesByFamilyIdentifierAndTypes($referenceEntityIdentifier, $attributeTypes);
        $locales = $this->findLocales->findAllActivated();
        $channels = $this->findChannels->findAll();

        $valueKeys = [];

        foreach ($attributes as $attribute) {
            if ('1' === $attribute['value_per_channel']
                && '1' === $attribute['value_per_locale']
            ) {
                $valueKeys[] = $this->generateScopableAndLocalizableValueKeys($attribute['identifier'], $channels);
            } elseif (
                '1' === $attribute['value_per_channel']
                && '0' === $attribute['value_per_locale']
            ) {
                $valueKeys[] = $this->generateScopableValueKeys($attribute['identifier'], $channels);
            } elseif (
                '0' === $attribute['value_per_channel']
                && '1' === $attribute['value_per_locale']
            ) {
                $valueKeys[] = $this->generateLocalizableValueKeys($attribute['identifier'], $locales);
            } else {
                $valueKeys[] = [$attribute['identifier']];
            }
        }

        return array_merge(...$valueKeys);
    }

    private function findAttributesByFamilyIdentifierAndTypes(ReferenceEntityIdentifier $referenceEntityIdentifier, array $attributeTypes): array
    {
        $query = <<<SQL
            SELECT
                attribute.identifier,
                attribute.value_per_channel,
                attribute.value_per_locale
            FROM akeneo_reference_entity_attribute as attribute
            WHERE attribute.reference_entity_identifier = :reference_entity_identifier
            AND attribute.attribute_type IN (:types)
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'reference_entity_identifier' => $referenceEntityIdentifier,
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
    private function generateScopableAndLocalizableValueKeys(string $attributeIdentifier, array $channels): array
    {
        $valueKeys = [];

        foreach ($channels as $channel) {
            foreach ($channel->getLocaleCodes() as $localeCode) {
                $valueKeys[] = sprintf('%s_%s_%s', $attributeIdentifier, $channel->getCode(), $localeCode);
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
            static fn (Channel $channel) => sprintf('%s_%s', $attributeIdentifier, $channel->getCode()),
            $channels
        );
    }

    /**
     * @param $locales Locale[];
     */
    private function generateLocalizableValueKeys(string $attributeIdentifier, array $locales): array
    {
        return array_map(
            static fn (Locale $locale) => sprintf('%s_%s', $attributeIdentifier, $locale->getCode()),
            $locales
        );
    }

    private function getCacheKey(ReferenceEntityIdentifier $referenceEntityIdentifier, array $attributeTypes): string
    {
        return sprintf(
            '%s_%s',
            (string) $referenceEntityIdentifier,
            implode('_', $attributeTypes)
        );
    }
}
