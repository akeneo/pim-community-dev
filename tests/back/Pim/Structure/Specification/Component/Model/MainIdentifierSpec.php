<?php

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\MainIdentifier;
use PhpSpec\ObjectBehavior;

class MainIdentifierSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('my_identifier');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MainIdentifier::class);
    }

    public function it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_main_identifier(): void
    {
        $this->asString()->shouldReturn('my_identifier');
    }
}
