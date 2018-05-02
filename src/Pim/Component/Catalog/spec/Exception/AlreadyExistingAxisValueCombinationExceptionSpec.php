<?php

namespace spec\Pim\Component\Catalog\Exception;

use Pim\Component\Catalog\Exception\AlreadyExistingAxisValueCombinationException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AlreadyExistingAxisValueCombinationExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('foobar', 'VariantProduct', '[color,size]');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AlreadyExistingAxisValueCombinationException::class);
    }

    function it_creates_an_exception_message_during_instanciation()
    {
        $this->getMessage()->shouldReturn(
            'The VariantProduct "foobar" already have a value for the "[color,size]" axis combination.'
        );
    }

    function it_returns_the_identifier_of_the_entity_that_already_have_the_axis_combination()
    {
        $this->getEntityIdentifier()->shouldReturn('foobar');
    }
}
