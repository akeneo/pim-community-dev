<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use PhpSpec\ObjectBehavior;

class JobExecutionRowSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(1, 'jobName', 'export', '2021-11-02 13:20:27', 'admin', 'COMPLETED', 10);
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
            'started_at' => '2021-11-02 13:20:27',
            'username' => 'admin',
            'status' => 'COMPLETED',
            'warning_count' => 10,
        ]);
    }

    public function it_normalizes_itself_with_null_value()
    {
        $this->beConstructedWith(1, 'jobName', 'export', null, null, 'COMPLETED', 10);

        $this->normalize()->shouldReturn([
            'job_execution_id' => 1,
            'job_name' => 'jobName',
            'type' => 'export',
            'started_at' => null,
            'username' => null,
            'status' => 'COMPLETED',
            'warning_count' => 10,
        ]);
    }
}
