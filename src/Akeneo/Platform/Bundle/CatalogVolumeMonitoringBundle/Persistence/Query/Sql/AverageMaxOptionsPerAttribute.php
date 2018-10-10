<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxOptionsPerAttribute implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_options_per_attribute';

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
    public function fetch(): AverageMaxVolumes
    {
        $simpleSelect   = AttributeTypes::OPTION_SIMPLE_SELECT;
        $multipleSelect = AttributeTypes::OPTION_MULTI_SELECT;

        $sql = <<<SQL
            SELECT 
                CEIL(AVG(opa.count_options_per_attribute)) average,
                MAX(opa.count_options_per_attribute) max
            FROM (
                SELECT ao.attribute_id, COUNT(ao.id) as count_options_per_attribute
                FROM pim_catalog_attribute_option ao
                JOIN pim_catalog_attribute AS a ON ao.attribute_id = a.id
		        WHERE a.attribute_type IN ('$simpleSelect', '$multipleSelect')
                GROUP BY ao.attribute_id
            ) as opa
SQL;
        $result = $this->connection->query($sql)->fetch();
        $volume = new AverageMaxVolumes((int) $result['max'], (int) $result['average'], $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
