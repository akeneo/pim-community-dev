<?php

namespace Specification\Akeneo\Asset\Component\Model;

use Akeneo\Asset\Component\Model\ReferenceInterface;
use PhpSpec\ObjectBehavior;

class ReferenceSpec extends ObjectBehavior
{
    function it_is_a_reference_interface()
    {
        $this->shouldImplement(ReferenceInterface::class);
    }
}
