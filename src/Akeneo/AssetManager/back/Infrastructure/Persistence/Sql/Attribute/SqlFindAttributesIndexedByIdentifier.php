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
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAttributesIndexedByIdentifier implements FindAttributesIndexedByIdentifierInterface
{
    private Connection $sqlConnection;

    private AttributeHydratorRegistry $attributeHydratorRegistry;

    private array $cachedResults = [];

    public function __construct(Connection $sqlConnection, AttributeHydratorRegistry $attributeHydratorRegistry)
    {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
    }

    public function clearCache(): void
    {
        $this->cachedResults = [];
    }

    /**
     * List of attributes indexed by their identifier
     *
     * @return AbstractAttribute[]
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        if (!isset($this->cachedResults[$assetFamilyIdentifier->normalize()])) {
            $results = $this->fetchResult($assetFamilyIdentifier);
            $this->cachedResults[$assetFamilyIdentifier->normalize()] = $this->hydrateAttributes($results);
        }

        return $this->cachedResults[$assetFamilyIdentifier->normalize()];
    }

    private function fetchResult(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $query = <<<SQL
        SELECT
            identifier,
            code,
            asset_family_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            is_read_only,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_asset_manager_attribute
        WHERE asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['asset_family_identifier' => (string) $assetFamilyIdentifier]
        );
        $result = $statement->fetchAll();

        return $result === [] ? [] : $result;
    }

    /**
     * List of attributes indexed by their identifier
     *
     * @return AbstractAttribute[]
     */
    private function hydrateAttributes(array $results): array
    {
        $indexedAttributes = [];
        foreach ($results as $result) {
            $attribute = $this->attributeHydratorRegistry->getHydrator($result)->hydrate($result);
            $indexedAttributes[$attribute->getIdentifier()->normalize()] = $attribute;
        }

        return $indexedAttributes;
    }
}
