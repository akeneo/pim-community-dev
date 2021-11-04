<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use PhpSpec\ObjectBehavior;

class JobExecutionRowSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            1,
            'jobName',
            'export',
            new \DateTime('2021-11-02T11:20:27+02:00'),
            'admin',
            'COMPLETED',
            10,
            15,
            1,
            3,
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(JobExecutionRow::class);
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'job_execution_id' => 1,
            'job_name' => 'jobName',
            'type' => 'export',
            'started_at' => '2021-11-02T11:20:27+02:00',
            'username' => 'admin',
            'status' => 'COMPLETED',
            'warning_count' => 10,
            'error_count' => 15,
            'tracking' => [
                'current_step' => 1,
                'total_step' => 3,
            ],
        ]);
    }

    public function it_normalizes_itself_with_null_value()
    {
        $this->beConstructedWith(
            1,
            'jobName',
            'export',
            null,
            null,
            'COMPLETED',
            5,
            0,
            1,
            1,
        );

        $this->normalize()->shouldReturn([
            'job_execution_id' => 1,
            'job_name' => 'jobName',
            'type' => 'export',
            'started_at' => null,
            'username' => null,
            'status' => 'COMPLETED',
            'warning_count' => 5,
            'error_count' => 0,
            'tracking' => [
                'current_step' => 1,
                'total_step' => 1,
            ],
        ]);
    }
}
