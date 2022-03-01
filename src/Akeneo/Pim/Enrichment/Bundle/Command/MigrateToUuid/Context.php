<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Context
{
    public function __construct(private bool $dryRun, private bool $withStats)
    {
    }

    public function dryRun(): bool
    {
        return $this->dryRun;
    }

    public function withStats(): bool
    {
        return $this->withStats;
    }
}
