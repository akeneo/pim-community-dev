<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedAverageMaxOptionsPerAttribute implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_options_per_attribute';

    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
        $stmt->bindValue('volumeName', self::VOLUME_NAME, Types::STRING);
        $sqlResult = $stmt->executeQuery()->fetchAssociative();

        $maxValue = isset($sqlResult['max']) ? (int) $sqlResult['max'] : 0;
        $averageValue = isset($sqlResult['average']) ? (int) $sqlResult['average'] : 0;

        $volume = new AverageMaxVolumes($maxValue, $averageValue, self::VOLUME_NAME);

        return $volume;
    }
}
