<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Formatter\Property\Product;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\TranslatorInterface;

class GroupsPropertySpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Extension\Formatter\Property\Product\GroupsProperty');
    }
}
