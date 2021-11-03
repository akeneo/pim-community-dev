<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionTable;
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
                new JobExecutionRow(1, 'jobName', 'export', '2021-11-02 13:20:27', 'admin', 'COMPLETED', 10)
            ],
            1,
            2
        );

        $this->normalize()->shouldReturn([
            'rows' => [
                [
                    'job_execution_id' => 1,
                    'job_name' => 'jobName',
                    'type' => 'export',
                    'started_at' => '2021-11-02 13:20:27',
                    'username' => 'admin',
                    'status' => 'COMPLETED',
                    'warning_count' => 10,
                ]
            ],
            'matches_count' => 1,
            'total_count' => 2,
        ]);
    }

    public function it_can_be_constructed_only_with_a_list_of_job_execution_row()
    {
        $this->beConstructedWith([1], 5, 10);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
