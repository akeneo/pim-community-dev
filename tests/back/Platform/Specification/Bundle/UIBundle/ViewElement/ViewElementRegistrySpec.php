<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\ViewElement;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementInterface;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementRegistry;
use PhpSpec\ObjectBehavior;

class ViewElementRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ViewElementRegistry::class);
    }

    function it_registers_and_exposes_view_elements(ViewElementInterface $button, ViewElementInterface $input)
    {
        $this->add($input, 'form', 1);
        $this->add($button, 'form', 10);

        $this->get('form')->shouldReturn([1 => $input, 10 => $button]);
    }

    function it_returns_an_empty_array_when_no_elements_of_the_requested_type_exist(ViewElementInterface $button)
    {
        $this->add($button, 'form', 10);

        $this->get('page')->shouldReturn([]);
    }

    function it_registers_view_elements_with_the_given_type_and_position(
        ViewElementInterface $foo,
        ViewElementInterface $bar,
        ViewElementInterface $baz
    ) {
        $this->add($foo, 'homepage', 10);
        $this->add($bar, 'homepage', 2);
        $this->add($baz, 'another page', 2);

        $this->get('homepage')->shouldReturn(
            [
                2 => $bar,
                10 => $foo
            ]
        );
        $this->get('another page')->shouldReturn(
            [
                2 => $baz,
            ]
        );
    }

    function it_can_handle_duplicate_position(ViewElementInterface $foo, ViewElementInterface $bar)
    {
        $this->add($foo, 'qux', 2);
        $this->add($bar, 'qux', 2);

        $this->get('qux')->shouldReturn(
            [
                2 => $foo,
                3 => $bar
            ]
        );
    }
}
