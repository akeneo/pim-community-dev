<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering;

use PhpSpec\ObjectBehavior;

class DiffFactorySpec extends ObjectBehavior
{
    function it_creates_diff_instance()
    {
        $diff = $this->create('foo', 'bar');

        $diff->getA()->shouldBe(['foo']);
        $diff->getB()->shouldBe(['bar']);

        $diff = $this->create(['foo', 'bar', 'moo'], ['bar', 'moo']);

        $diff->getA()->shouldBe(['foo', 'bar', 'moo']);
        $diff->getB()->shouldBe(['bar', 'moo']);
    }
}
