<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Event;

use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use PhpSpec\ObjectBehavior;

class TechnicalErrorEventSpec extends ObjectBehavior
{
    public function let(\Exception $error): void
    {
        $this->beConstructedWith($error);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(TechnicalErrorEvent::class);
    }

    public function it_returns_the_error($error): void
    {
        $this->getError()->shouldReturn($error);
    }
}
