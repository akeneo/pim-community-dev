<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait StatusAwareTrait
{
    protected string $status = 'none';
    private ?float $startTime = null;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatusInProgress(): void
    {
        $this->startTime = microtime(true);
        $this->status = 'in_progress';
    }

    public function setStatusDone(): void
    {
        $this->status = 'done';
    }

    public function setStatusInError(): void
    {
        $this->status = 'in_error';
    }

    public function getDuration(): ?float
    {
        if ($this->startTime === null) {
            return null;
        }
        return microtime(true) - $this->startTime;
    }
}
