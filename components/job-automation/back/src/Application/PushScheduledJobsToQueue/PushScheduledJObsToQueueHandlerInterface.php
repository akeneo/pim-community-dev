<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue;

interface PushScheduledJObsToQueueHandlerInterface
{
    public function handle(PushScheduledJobsToQueueQuery $query): void;
}
