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

use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyQuery;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyItemsInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorAssetFamilyHydrator;
use Doctrine\DBAL\Connection;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorAssetFamilyItems implements FindConnectorAssetFamilyItemsInterface
{
    private Connection $connection;

    private ConnectorAssetFamilyHydrator $assetFamilyHydrator;

    public function __construct(
        Connection $connection,
        ConnectorAssetFamilyHydrator $hydrator
    ) {
        $this->connection = $connection;
        $this->assetFamilyHydrator = $hydrator;
    }

    public function find(AssetFamilyQuery $query): array
    {
        $sql = <<<SQL
        SELECT
            re.identifier,
            re.labels,
            ama.code AS attribute_as_main_media,
            fi.file_key as image_file_key,
            fi.original_filename as image_original_filename,
            re.rule_templates,
            re.transformations,
            re.naming_convention
        FROM akeneo_asset_manager_asset_family as re
        LEFT JOIN akeneo_file_storage_file_info AS fi ON fi.file_key = re.image
        LEFT JOIN akeneo_asset_manager_attribute ama ON re.attribute_as_main_media = ama.identifier
        %s
        ORDER BY identifier ASC
        LIMIT :search_after_limit
SQL;
        $sql = $this->queryIsFirstPage($query) ?
            sprintf($sql, '') :
            sprintf($sql, 'WHERE re.identifier > :search_after_identifier');

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'search_after_identifier' => $query->getSearchAfterIdentifier(),
                'search_after_limit' => $query->getSize()
            ],
            [
                'search_after_identifier' => \PDO::PARAM_STR,
                'search_after_limit' => \PDO::PARAM_INT
            ]
        );

        $results = $statement->fetchAll();

        if (empty($results)) {
            return [];
        }

        $hydratedAssetFamilies = [];

        foreach ($results as $result) {
            $hydratedAssetFamilies[] = $this->assetFamilyHydrator->hydrate($result);
        }

        return $hydratedAssetFamilies;
    }

    private function queryIsFirstPage(AssetFamilyQuery $query): bool
    {
        return empty($query->getSearchAfterIdentifier());
    }
}
