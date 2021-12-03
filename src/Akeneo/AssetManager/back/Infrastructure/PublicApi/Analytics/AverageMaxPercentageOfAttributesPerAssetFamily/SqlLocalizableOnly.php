<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily;

use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlLocalizableOnly
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
  MAX(number_of_localizable_only_attributes_per_asset_family * 100 / number_of_attributes) as max,
  CEIL(AVG(number_of_localizable_only_attributes_per_asset_family * 100 / number_of_attributes)) as average
FROM (
  SELECT asset_family_identifier,
         SUM(value_per_locale = 1 AND value_per_channel = 0) as number_of_localizable_only_attributes_per_asset_family,
         COUNT(*) as number_of_attributes
  FROM akeneo_asset_manager_attribute 
  GROUP BY asset_family_identifier
) AS rec;
SQL;
        $result = $this->sqlConnection->executeQuery($sql)->fetchAssociative();

        return new AverageMaxVolumes(
            (int) $result['max'],
            (int) $result['average']
        );
    }
}
