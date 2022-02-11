<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Bundle\Command;

trait MigrateToUuidTrait
{
    protected function tableExists(string $tableName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            'SHOW TABLES LIKE :tableName',
            ['tableName' => $tableName]
        );

        return count($rows) >= 1;
    }
}
