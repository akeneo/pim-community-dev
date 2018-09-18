<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Repository\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Repository\AggregatedVolumeRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedVolumeRepository implements AggregatedVolumeRepositoryInterface
{
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
    public function add(AggregatedVolume $aggregatedVolume): void
    {
        $sql = <<<SQL
REPLACE INTO pim_aggregated_volume (volume_name, volume, aggregated_at)
VALUES (:volumeName, :volume, :aggregatedAt) 
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('volumeName', $aggregatedVolume->getVolumeName(), Type::STRING);
        $stmt->bindValue('volume', $aggregatedVolume->getVolume(), Type::JSON_ARRAY);
        $stmt->bindValue('aggregatedAt', $aggregatedVolume->aggregatedAt(), Type::DATETIME);

        $stmt->execute();
    }
}
