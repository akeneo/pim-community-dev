<?php

namespace spec\Pim\Component\Catalog\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class UniqueValuesSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\UniqueValuesSet');
    }
}
