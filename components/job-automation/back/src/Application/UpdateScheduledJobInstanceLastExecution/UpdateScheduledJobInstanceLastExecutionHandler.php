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

namespace Akeneo\Platform\JobAutomation\Application\UpdateScheduledJobInstanceLastExecution;

use Akeneo\Platform\JobAutomation\Domain\ClockInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\UpdateAutomationLastExecutionDateQueryInterface;

class UpdateScheduledJobInstanceLastExecutionHandler implements UpdateScheduledJobInstanceLastExecutionHandlerInterface
{
    public function __construct(
        private ClockInterface $clock,
        private UpdateAutomationLastExecutionDateQueryInterface $updateJobInstanceAutomationLastExecutionDate,
    ) {
    }

    public function handle(UpdateScheduledJobInstanceLastExecutionCommand $command): void
    {
        $lastExecutionDate = $this->clock->now();

        $this->updateJobInstanceAutomationLastExecutionDate->forJobInstanceCode($command->jobInstanceCode, $lastExecutionDate);
    }
}
