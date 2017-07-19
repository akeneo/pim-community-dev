<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModel::class);
    }
}
