<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Rendering;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DiffFactorySpec extends ObjectBehavior
{
    function it_creates_diff_instance()
    {
        $diff = $this->create('foo', 'bar');

        $diff->getA()->shouldBe(['foo']);
        $diff->getB()->shouldBe(['bar']);
    }
}
