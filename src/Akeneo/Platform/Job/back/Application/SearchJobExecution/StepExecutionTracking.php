<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Domain\Model\Status;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class StepExecutionTracking
{
    public function __construct(
        private int $id,
        private int $duration,
        private int $warningCount,
        private int $errorCount,
        private int $totalItems,
        private int $processedItems,
        private bool $isTrackable,
        private Status $status,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function normalize(): array
    {
        return [
            'id' => $this->id,
            'duration' => $this->duration,
            'warning_count' => $this->warningCount,
            'error_count' => $this->errorCount,
            'total_items' => $this->totalItems,
            'processed_items' => $this->processedItems,
            'is_trackable' => $this->isTrackable,
            'status' => $this->status->getLabel(),
        ];
    }
}
