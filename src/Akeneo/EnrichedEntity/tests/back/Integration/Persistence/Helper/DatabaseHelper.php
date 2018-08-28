<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Helper;

use Doctrine\DBAL\Connection;

/**
 * This class is responsible for helping calling web routes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DatabaseHelper
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function resetDatabase(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_attribute;
            DELETE FROM akeneo_enriched_entity_record;
            DELETE FROM akeneo_enriched_entity_enriched_entity;
            DELETE FROM pim_catalog_attribute_group;
            DELETE FROM pim_catalog_attribute;
SQL;

        $this->sqlConnection->executeQuery($resetQuery);
    }
}
