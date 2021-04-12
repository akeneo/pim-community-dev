<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Bundle\MessengerBundle\Message\OrderedMessageInterface;
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

    function it_implements_ordered_message_interface()
    {
        $this->shouldImplement(OrderedMessageInterface::class);
    }

    function it_returns_the_ordering_key()
    {
        $this->getOrderingKey()->shouldBe('job_key');
    }
}
