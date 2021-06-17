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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetSingleLinkType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlAssetFamilyIsLinkedToAtLeastOneProductAttribute implements AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function isLinked(AssetFamilyIdentifier $identifier): bool
    {
        return $this->isAssetFamilyLinkedToAtLeastOneProductAttribute($identifier);
    }

    private function fetchResults(): array
    {
        $query = <<<SQL
        SELECT properties
        FROM pim_catalog_attribute
        WHERE attribute_type IN (:attribute_types)
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'attribute_types' => [
                    AssetCollectionType::ASSET_COLLECTION
                ]
            ],
            [
                'attribute_types' => Connection::PARAM_STR_ARRAY
            ]
        );

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $results;
    }

    private function isAssetFamilyLinkedToAtLeastOneProductAttribute(AssetFamilyIdentifier $identifier): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $results = $this->fetchResults();
        $linkedAssets = [];

        foreach ($results as $result) {
            $properties = Type::getType(Type::TARRAY)->convertToPhpValue($result['properties'], $platform);
            $linkedAssets[] = $properties['reference_data_name'];
        }

        return in_array((string) $identifier, array_unique($linkedAssets));
    }
}
