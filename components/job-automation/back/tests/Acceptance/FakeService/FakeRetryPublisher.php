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

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\FakeService;

use Akeneo\Platform\JobAutomation\Domain\Model\DueJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Publisher\RetryPublisherInterface;

final class FakeRetryPublisher implements RetryPublisherInterface
{
    public array $dueJobInstances = [];

    public function publish(DueJobInstance $dueJobInstance): void
    {
        $this->dueJobInstances[] = $dueJobInstance;
        return;
    }
}
