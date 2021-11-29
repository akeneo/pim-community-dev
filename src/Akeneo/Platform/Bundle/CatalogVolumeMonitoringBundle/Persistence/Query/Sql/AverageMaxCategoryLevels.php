<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxCategoryLevels implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_category_levels';

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
        SELECT 	
          MAX(lvl) as max,
    	  CEIL(AVG(lvl)) as average 
        FROM pim_catalog_category;
SQL;
        $result = $this->connection->executeQuery($sql)->fetchAssociative();

        $volume = new AverageMaxVolumes((int) $result['max'], (int) $result['average'], self::VOLUME_NAME);

        return $volume;
    }
}
