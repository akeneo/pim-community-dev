<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Doctrine\DBAL\Connection;

/**
 * Count the total number of product values for products and product models together.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedCountProductAndProductModelValues implements CountQuery
{
    private const VOLUME_NAME = 'count_product_and_product_model_values';

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
SELECT SUM(JSON_EXTRACT(volume, '$.value')) AS value
FROM pim_aggregated_volume WHERE volume_name IN ('count_product_values', 'count_product_model_values')
SQL;

        $sqlResult = $this->connection->query($sql)->fetch();
        $volumeValue = isset($sqlResult['value']) ? (int) $sqlResult['value'] : 0;

        return new CountVolume($volumeValue, $this->limit, self::VOLUME_NAME);
    }
}
