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
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyByAssetFamilyIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorAssetFamilyHydrator;
use Doctrine\DBAL\Connection;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorAssetFamilyByAssetFamilyIdentifier implements FindConnectorAssetFamilyByAssetFamilyIdentifierInterface
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

    public function find(AssetFamilyIdentifier $identifier, bool $caseSensitive = true): ?ConnectorAssetFamily
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
        LEFT JOIN akeneo_asset_manager_attribute ama ON re.attribute_as_main_media = ama.identifier
        LEFT JOIN akeneo_file_storage_file_info AS fi ON fi.file_key = re.image
        WHERE {binary} re.identifier = :identifier;
SQL;
        $sql = str_replace('{binary}', $caseSensitive ? 'BINARY' : '', $sql);

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'identifier' => (string) $identifier,
            ]
        );

        $result = $statement->fetch();

        if (empty($result)) {
            return null;
        }

        return $this->assetFamilyHydrator->hydrate($result);
    }
}
