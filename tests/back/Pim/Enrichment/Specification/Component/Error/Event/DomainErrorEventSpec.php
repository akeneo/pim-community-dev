<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\Event;

use Akeneo\Pim\Enrichment\Component\Error\Event\DomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use PhpSpec\ObjectBehavior;

class DomainErrorEventSpec extends ObjectBehavior
{
    public function let(DomainErrorInterface $error): void
    {
        $this->beConstructedWith($error);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DomainErrorEvent::class);
    }

    public function it_returns_the_error($error): void
    {
        $this->getError()->shouldReturn($error);
    }
}
