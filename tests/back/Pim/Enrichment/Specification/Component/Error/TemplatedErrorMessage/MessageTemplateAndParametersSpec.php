<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage;

use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use PhpSpec\ObjectBehavior;

class TemplatedErrorMessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Error message with {first} param and {second} param.', [
            'first' => 'a first',
            'second' => 'a second'
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(TemplatedErrorMessage::class);
    }

    function it_returns_the_message_template()
    {
        $this->getTemplate()->shouldReturn('Error message with {first} param and {second} param.');
    }

    function it_returns_the_message_parameters()
    {
        $this->getParameters()->shouldReturn([
            'first' => 'a first',
            'second' => 'a second'
        ]);
    }

    function it_returns_the_message()
    {
        $this->__toString()->shouldReturn('Error message with a first param and a second param.');
    }

    function it_validates_the_message_parameters_type()
    {
        $this->beConstructedWith('My {key} param', ['key' => []]);
        $this
            ->shouldThrow(new \InvalidArgumentException(
                'Message parameter "{key}" must be of type string, array given.'
            ))
            ->duringInstantiation();
    }

    function it_validates_the_message_template_with_the_parameters()
    {
        $this->beConstructedWith('My key param', ['key' => 'test']);
        $this
            ->shouldThrow(new \InvalidArgumentException(
                'Message parameter "{key}" was not found in the message template "My key param".'
            ))
            ->duringInstantiation();
    }
}
