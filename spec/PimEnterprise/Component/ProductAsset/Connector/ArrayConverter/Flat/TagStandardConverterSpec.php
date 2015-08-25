<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Exception\ArrayConversionException;

class TagStandardConverterSpec extends ObjectBehavior
{
    function it_converts()
    {
        $fields = [
            'qualification' => 'dog,flowers'
        ];

        $this->convert($fields)->shouldReturn([
            'tags' => ['dog', 'flowers']
        ]);
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "qualification" is expected, provided fields are "not_a_code"'))->during(
            'convert',
            [['not_a_code' => '']]
        );
    }
}
