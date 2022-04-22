<?php

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure;

use Akeneo\Platform\JobAutomation\Infrastructure\Dummy;
use PhpSpec\ObjectBehavior;

class DummySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('Hello world!');
        $this->shouldBeAnInstanceOf(Dummy::class);
    }

    public function it_returns_message(): void
    {
        $this->getMessage()->shouldReturn('Hello world!');
    }
}
