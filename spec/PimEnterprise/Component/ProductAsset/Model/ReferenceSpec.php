<?php

namespace spec\PimEnterprise\Component\ProductAsset\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferenceSpec extends ObjectBehavior
{
    function it_is_a_reference_interface()
    {
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Model\ReferenceInterface');
    }
}
