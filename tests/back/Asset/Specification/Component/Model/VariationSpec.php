<?php

namespace Specification\Akeneo\Asset\Component\Model;

use Akeneo\Asset\Component\Model\VariationInterface;
use PhpSpec\ObjectBehavior;

class VariationSpec extends ObjectBehavior
{
    function it_is_a_variation_interface()
    {
        $this->shouldImplement(VariationInterface::class);
    }
}
