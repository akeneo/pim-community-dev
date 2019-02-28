<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlCountReferenceEntities implements CountQuery
{
    private const VOLUME_NAME = 'count_reference_entity';

    /** @var Connection */
    private $sqlConnection;

    /** @var int */
    private $limit;

    public function __construct(Connection $sqlConnection, int $limit)
    {
        $this->sqlConnection = $sqlConnection;
        $this->limit = $limit;
    }

    public function fetch(): CountVolume
    {
        $sql = <<<SQL
            SELECT COUNT(*) as count
            FROM akeneo_reference_entity_reference_entity;
SQL;
        $result = $this->sqlConnection->query($sql)->fetch();
        $volume = new CountVolume((int) $result['count'], $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
