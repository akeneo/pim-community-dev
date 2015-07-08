<?php

namespace spec\PimEnterprise\Component\ProductAsset\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;

class AssetStandardConverterSpec extends ObjectBehavior
{
    function it_converts()
    {
        $fields = [
            'code'          => 'mycode',
            'localized'     => 0,
            'description'   => 'My awesome description',
            'qualification' => 'dog,flowers',
            'end_of_use_at' => '2018-02-01',
        ];

        $this->convert($fields)->shouldReturn([
            'tags'          => ['dog', 'flowers'],
            'code'          => 'mycode',
            'localized'     => false,
            'description'   => 'My awesome description',
            'end_of_use_at' => '2018-02-01',
        ]);
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))->during(
            'convert',
            [['not_a_code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_localized_is_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "localized" is expected, provided fields are "code, optional"'))->during(
            'convert',
            [['code' => 'mycode', 'optional' => 'value']]
        );
    }

    function it_throws_an_exception_if_required_field_code_is_empty()
    {
        $this->shouldThrow(new \LogicException('Field "code" must be filled'))->during(
            'convert',
            [['code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_localizable_does_not_contain_valid_value()
    {
        $this->shouldThrow(new \LogicException('Field "code" must be filled'))->during(
            'convert',
            [['code' => '']]
        );
    }
}
