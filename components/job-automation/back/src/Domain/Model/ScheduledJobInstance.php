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

namespace Akeneo\Platform\JobAutomation\Domain\Model;

final class ScheduledJobInstance
{
    public function __construct(
        public string $code,
        public string $jobName,
        public string $type,
        public array $rawParameters,
        public bool $isScheduled,
        public string $cronExpression,
        public \DateTimeImmutable $setupDate,
        public ?\DateTimeImmutable $lastExecutionDate,
    ) {
    }
}
