<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Exception\AlreadyExistingAxisValueCombinationException;

class AlreadyExistingAxisValueCombinationExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('foobar', 'an exception message');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AlreadyExistingAxisValueCombinationException::class);
    }

    function it_adds_an_exception_message_during_instanciation()
    {
        $this->getMessage()->shouldReturn('an exception message');
    }

    function it_returns_the_identifier_of_the_entity_that_already_have_the_axis_combination()
    {
        $this->getEntityIdentifier()->shouldReturn('foobar');
    }
}
