<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Query\GetJobExecutionStatusInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class InMemoryGetJobExecutionStatus implements GetJobExecutionStatusInterface
{
    public array $statuses = [];

    public function getByJobExecutionId(int $jobExecutionId): ?BatchStatus
    {
        return $this->statuses[$jobExecutionId] ?? null;
    }

    public function setJobExecutionIdStatus(int $jobExecutionId, ?BatchStatus $status): void
    {
        $this->statuses[$jobExecutionId] = $status;
    }
}
