<?php

namespace spec\PimEnterprise\Component\ProductAsset\Model;

use PhpSpec\ObjectBehavior;

class VariationSpec extends ObjectBehavior
{
    function it_is_a_variation_interface()
    {
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Model\VariationInterface');
    }
}
