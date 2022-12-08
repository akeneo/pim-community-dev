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

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\UseCases;

use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueHandlerInterface;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueQuery;
use Akeneo\Platform\JobAutomation\Domain\Model\DueJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\JobAutomation\Test\Acceptance\FakeService\FakeRetryPublisher;

final class PushScheduledJobsToQueueTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_push_job_instance_in_queue(): void
    {
        $date = new \DateTimeImmutable('now');

        $this->getHandler()->handle(new PushScheduledJobsToQueueQuery([
            new ScheduledJobInstance(
                code: 'scheduled_job_instance_1',
                label: 'scheduled job instance 1',
                type: 'import',
                rawParameters: [],
                notifiedUsers: [],
                notifiedUserGroups: [],
                cronExpression: '0 */4 * * *',
                setupDate: $date->sub(new \DateInterval('PT5H')),
                lastExecutionDate: null,
                runningUsername: 'job_automated_scheduled_job_instance_1'
            ),
            new ScheduledJobInstance(
                code: 'scheduled_job_instance_2',
                label: 'scheduled job instance 2',
                type: 'import',
                rawParameters: [],
                notifiedUsers: [],
                notifiedUserGroups: [],
                cronExpression: '0 */4 * * *',
                setupDate: $date->add(new \DateInterval('PT5H')),
                lastExecutionDate: null,
                runningUsername: 'job_automated_scheduled_job_instance_2'
            )
        ]));

        /** @var DueJobInstance[] $dueJobInstances */
        $dueJobInstances = $this->getPublisher()->dueJobInstances;

        $this->assertEquals(1, \count($dueJobInstances));
        $this->assertEquals("scheduled_job_instance_1", $dueJobInstances[0]->scheduledJobInstance->code);
    }

    public function getHandler(): PushScheduledJobsToQueueHandlerInterface
    {
        return $this->get('akeneo.job_automation.handler.push_scheduled_jobs_to_queue_handler');
    }

    public function getPublisher(): FakeRetryPublisher
    {
        return $this->get('Akeneo\Platform\JobAutomation\Domain\Publisher\PublisherInterface');
    }
}
