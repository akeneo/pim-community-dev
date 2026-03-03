<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Doctrine\DBAL\Connection;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountProductValues implements CountQuery
{
    private const VOLUME_NAME = 'count_product_values';

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
    public function fetch(): CountVolume
    {
        $sql = <<<SQL
           SELECT SUM(JSON_LENGTH(JSON_EXTRACT(raw_values, '$.*.*.*'))) as sum_product_values
           FROM pim_catalog_product
SQL;
        $result = $this->connection->executeQuery($sql)->fetchAssociative();

        $volume = new CountVolume((int) $result['sum_product_values'], self::VOLUME_NAME);

        return $volume;
    }
}
