<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTable;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PhpSpec\ObjectBehavior;

class JobExecutionTableSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith([], 5, 10);
        $this->shouldBeAnInstanceOf(JobExecutionTable::class);
    }

    public function it_normalizes_itself()
    {
        $this->beConstructedWith(
            [
                new JobExecutionRow(
                    1,
                    'jobName',
                    'export',
                    new \DateTimeImmutable('2021-11-02T11:20:27+02:00'),
                    'admin',
                    Status::fromLabel('COMPLETED'),
                    true,
                    new JobExecutionTracking(1, 2, []),
                ),
            ],
            1,
        );

        $this->normalize()->shouldReturn([
            'rows' => [
                [
                    'job_execution_id' => 1,
                    'job_name' => 'jobName',
                    'type' => 'export',
                    'started_at' => '2021-11-02T11:20:27+02:00',
                    'username' => 'admin',
                    'status' => 'COMPLETED',
                    'warning_count' => 0,
                    'has_error' => false,
                    'tracking' => [
                        'current_step' => 1,
                        'total_step' => 2,
                        'steps' => [],
                    ],
                    'is_stoppable' => true,
                ],
            ],
            'matches_count' => 1,
        ]);
    }

    public function it_can_be_constructed_only_with_a_list_of_job_execution_row()
    {
        $this->beConstructedWith([1], 5);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
