<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedCountProductValues implements CountQuery
{
    private const VOLUME_NAME = 'count_product_values';

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

    public function fetch(): CountVolume
    {
        $sql = <<<SQL
SELECT JSON_EXTRACT(volume, '$.value') AS value 
FROM pim_aggregated_volume WHERE volume_name = :volumeName;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('volumeName', self::VOLUME_NAME, Type::STRING);
        $stmt->execute();
        $sqlResult = $stmt->fetch();

        $volumeValue = isset($sqlResult['value']) ? (int) $sqlResult['value'] : 0;

        return new CountVolume($volumeValue, $this->limit, self::VOLUME_NAME);
    }
}
