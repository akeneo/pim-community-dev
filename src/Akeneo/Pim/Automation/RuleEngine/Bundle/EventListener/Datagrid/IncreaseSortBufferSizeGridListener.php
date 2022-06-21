<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\EventListener\Datagrid;

use Doctrine\DBAL\Connection;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

final class IncreaseSortBufferSizeGridListener
{
    public function __construct(private Connection $connection)
    {
    }

    public function configure(BuildAfter $event)
    {
        $this->connection->executeQuery('SET SESSION sort_buffer_size = 1000000;');
    }
}
