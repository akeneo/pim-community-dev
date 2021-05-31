<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Analytics;

use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfAttributesPerAssetFamily
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetch(): AverageMaxVolumes
    {
        $sql = <<<SQL
SELECT 
  MAX(number_of_attributes_per_asset_family) as max,
  CEIL(AVG(number_of_attributes_per_asset_family)) as average
FROM (
  SELECT asset_family.identifier, COUNT(code) as number_of_attributes_per_asset_family
  FROM akeneo_asset_manager_asset_family asset_family
    LEFT JOIN akeneo_asset_manager_attribute attribute
    ON asset_family.identifier = attribute.asset_family_identifier
  GROUP BY asset_family.identifier
) as rec;
SQL;
        $result = $this->sqlConnection->query($sql)->fetch();

        return new AverageMaxVolumes(
            (int) $result['max'],
            (int) $result['average']
        );
    }
}
