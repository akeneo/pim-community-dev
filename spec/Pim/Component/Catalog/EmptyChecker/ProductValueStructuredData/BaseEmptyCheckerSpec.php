<?php

namespace spec\Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData;

use PhpSpec\ObjectBehavior;

class BaseEmptyCheckerSpec extends ObjectBehavior
{
    function it_is_a_product_value_empty_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData\EmptyCheckerInterface');
    }

    function it_supports_all_native_attributes($whateverAttributeCode)
    {
        $this->supports($whateverAttributeCode)->shouldReturn(true);
    }

    function it_checks_not_empty_structured_data()
    {
        $this->isEmpty('not_empty_string', 'notemptydata')->shouldReturn(false);
        $this->isEmpty('not_null', 42)->shouldReturn(false);
        $this->isEmpty('not_empty_array', ['red'])->shouldReturn(false);
        $this->isEmpty('not_empty_price', [['currency' => 'EUR', 'data' => null], ['currency' => 'USD', 'data' => 12]])
            ->shouldReturn(false);
        $this->isEmpty('not_empty_metric', ['unit' => 'KILOGRAM', 'data' => 12])->shouldReturn(false);
        $this->isEmpty('not_empty_file', ['filePath' => 'toto.png', 'originalFilename' => 'tata.png'])
            ->shouldReturn(false);
    }

    function it_checks_empty_structured_data()
    {
        $this->isEmpty('null', null)->shouldReturn(true);
        $this->isEmpty('empty_string', '')->shouldReturn(true);
        $this->isEmpty('empty_array', [])->shouldReturn(true);
        $this->isEmpty('empty_price', [['currency' => 'EUR', 'data' => null], ['currency' => 'USD', 'data' => null]])
            ->shouldReturn(true);
        $this->isEmpty('empty_metric', ['unit' => 'KILOGRAM', 'data' => null])->shouldReturn(true);
        $this->isEmpty('empty_file', ['filePath' => null, 'originalFilename' => null])->shouldReturn(true);
    }
}
