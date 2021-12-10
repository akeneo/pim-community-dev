<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics;

use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlCountReferenceEntities
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    public function fetch(): CountVolume
    {
        $sql = <<<SQL
            SELECT COUNT(*) as count
            FROM akeneo_reference_entity_reference_entity;
SQL;
        $result = $this->sqlConnection->query($sql)->fetch();

        return new CountVolume((int) $result['count']);
    }
}
