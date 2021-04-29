<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

final class MysqlChecker
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function status(): ServiceStatus
    {
        try {
            $this->connection->executeQuery("SELECT 'ok' FROM pim_catalog_product_unique_data LIMIT 1")->fetchAll();

            return ServiceStatus::ok();
        } catch (DBALException $e) {
            return ServiceStatus::notOk(sprintf('Unable to request the database: "%s".', $e->getMessage()));
        }
    }
}
