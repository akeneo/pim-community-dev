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
use Psr\Log\LoggerInterface;

final class MysqlChecker
{
    private Connection $connection;
    private LoggerInterface $logger;

    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function status(): ServiceStatus
    {
        try {
            $this->connection->executeQuery("SELECT 'ok' FROM pim_catalog_product_unique_data LIMIT 1")->fetchAllAssociative();

            return ServiceStatus::ok();
        } catch (\Throwable $e) {
            $this->logger->error("MySql ServiceCheck error", ['exception' => $e]);
            return ServiceStatus::notOk(sprintf('MySQL exception: "%s".', $e->getMessage()));
        }
    }
}
