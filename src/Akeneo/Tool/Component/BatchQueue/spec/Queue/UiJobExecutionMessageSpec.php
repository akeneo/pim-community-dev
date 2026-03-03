<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use PhpSpec\ObjectBehavior;

class UiJobExecutionMessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('createJobExecutionMessage', [
            1,
            ['option1' => 'value1'],
        ]);
    }

    function it_is_a_job_message()
    {
        $this->shouldImplement(JobExecutionMessageInterface::class);
    }
}
