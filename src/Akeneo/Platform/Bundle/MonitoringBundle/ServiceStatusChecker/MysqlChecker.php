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

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatus;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;
use Doctrine\DBAL\Connection;

final class MysqlChecker implements ServiceStatusChecker
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function status(): ServiceStatus
    {
        if ($this->connection->ping()) {
            return new ServiceStatus(true, "OK");
        } else {
            return new ServiceStatus(false, "Unable to ping the database.");
        }
    }
}
