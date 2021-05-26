<?php


namespace Akeneo\AssetManager\Infrastructure\PublicApi\Analytics;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedAverageMaxNumberOfValuePerAsset implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_number_of_values_per_asset';

    private Connection $connection;

    private int $limit;

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
SELECT JSON_EXTRACT(volume, '$.value.max') AS max, JSON_EXTRACT(volume, '$.value.average') AS average
FROM pim_aggregated_volume WHERE volume_name = :volumeName;
SQL;
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('volumeName', self::VOLUME_NAME, Type::STRING);
        $stmt->execute();
        $sqlResult = $stmt->fetch();

        $maxValue = isset($sqlResult['max']) ? (int) $sqlResult['max'] : 0;
        $averageValue = isset($sqlResult['average']) ? (int) $sqlResult['average'] : 0;

        $volume = new AverageMaxVolumes($maxValue, $averageValue, $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
