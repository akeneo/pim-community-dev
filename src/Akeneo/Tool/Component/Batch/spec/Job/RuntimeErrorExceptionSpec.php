<?php

namespace spec\Akeneo\Component\Batch\Job;

use PhpSpec\ObjectBehavior;

class RuntimeErrorExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('my message %myparam%', ['%myparam%' => 'param']);
    }

    function it_provides_message_parameters()
    {
        $this->getMessageParameters()->shouldReturn(['%myparam%' => 'param']);
    }
}
