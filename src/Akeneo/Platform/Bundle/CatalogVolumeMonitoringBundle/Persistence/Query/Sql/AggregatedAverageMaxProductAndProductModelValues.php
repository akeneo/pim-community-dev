<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * Calculate the maximum and average number of product values for products and product models together.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedAverageMaxProductAndProductModelValues implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_product_and_product_model_values';

    /** @var Connection */
    private $connection;

    /** @var int */
    private $limit;

    /**
     * @param Connection $connection
     * @param int        $limit
     */
    public function __construct(Connection $connection, int $limit)
    {
        $this->connection = $connection;
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): AverageMaxVolumes
    {
        $sql = <<<SQL
SELECT 
(
    SELECT MAX(JSON_EXTRACT(volume, '$.value.max'))
    FROM pim_aggregated_volume
    WHERE volume_name IN ('average_max_product_values', 'average_max_product_model_values')
) AS max,
CEIL(
    (
        SELECT SUM(JSON_EXTRACT(volume, '$.value'))
        FROM pim_aggregated_volume
        WHERE volume_name IN ('count_product_values', 'count_product_model_values')
    ) /
    (
        SELECT SUM(JSON_EXTRACT(volume, '$.value'))
        FROM pim_aggregated_volume
        WHERE volume_name IN ('count_products', 'count_product_models')
    )
) AS average
SQL;
        $sqlResult = $this->connection->query($sql)->fetch();
        $maxValue = isset($sqlResult['max']) ? (int) $sqlResult['max'] : 0;
        $averageValue = isset($sqlResult['average']) ? (int) $sqlResult['average'] : 0;

        return new AverageMaxVolumes($maxValue, $averageValue, $this->limit, self::VOLUME_NAME);
    }
}
