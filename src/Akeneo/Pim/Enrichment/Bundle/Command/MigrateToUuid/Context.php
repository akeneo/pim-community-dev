<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\LogContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Context
{
    public LogContext $logContext;

    public function __construct(
        private bool $dryRun,
        private bool $withStats,
        private bool $lockTables
    ) {
    }

    public function dryRun(): bool
    {
        return $this->dryRun;
    }

    public function withStats(): bool
    {
        return $this->withStats;
    }

    public function lockTables(): bool
    {
        return $this->lockTables;
    }
}
