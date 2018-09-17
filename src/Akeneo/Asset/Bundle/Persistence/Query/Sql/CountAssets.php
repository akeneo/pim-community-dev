<?php

declare(strict_types=1);

namespace Akeneo\Asset\Bundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountAssets implements CountQuery
{
    private const VOLUME_NAME = 'count_assets';

    /** @var Connection */
    private $connection;

    /** @var int */
    private $limit;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection, int $limit)
    {
        $this->connection = $connection;
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): CountVolume
    {
        $sql = <<<SQL
            SELECT COUNT(*) as count
            FROM pimee_product_asset_asset;
SQL;
        $result = $this->connection->query($sql)->fetch();
        $volume = new CountVolume((int) $result['count'], $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
