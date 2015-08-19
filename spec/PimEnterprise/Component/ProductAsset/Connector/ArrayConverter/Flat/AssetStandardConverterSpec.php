<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Exception\ArrayConversionException;

class AssetStandardConverterSpec extends ObjectBehavior
{
    function it_converts()
    {
        $fields = [
            'code'          => 'mycode',
            'localized'     => 0,
            'description'   => 'My awesome description',
            'qualification' => 'dog,flowers',
            'categories'    => 'cat1,cat2,cat3',
            'end_of_use'    => '2018-02-01',
        ];

        $this->convert($fields)->shouldReturn([
            'tags'        => ['dog', 'flowers'],
            'categories'  => ['cat1', 'cat2', 'cat3'],
            'code'        => 'mycode',
            'localized'   => false,
            'description' => 'My awesome description',
            'end_of_use'  => '2018-02-01',
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
        $this->shouldThrow(new \LogicException('Field "localized" is expected, provided fields are "code, optional"'))
            ->during('convert', [['code' => 'mycode', 'optional' => 'value']]);
    }

    function it_throws_an_exception_if_required_field_code_is_empty()
    {
        $this->shouldThrow(new \LogicException('Field "code" must be filled'))->during(
            'convert',
            [['code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_localized_does_not_contain_valid_value()
    {
        $fields = [
            'code'          => 'mycode',
            'localized'     => 'y',
            'description'   => 'My awesome description',
            'qualification' => 'dog,flowers',
            'end_of_use'    => '2018-02-01',
        ];

        $this->shouldThrow(
            new ArrayConversionException('Localized field contains invalid data only "0" or "1" is accepted')
        )->during('convert',[$fields]);
    }
}
