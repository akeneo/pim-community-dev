<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Exception\DataArrayConversionException;

class AssetSpec extends ObjectBehavior
{
    const TEST_TIMEZONE = 'Europe/Paris';

    protected $userTimezone;

    function let()
    {
        $this->userTimezone = date_default_timezone_get();
        date_default_timezone_set(self::TEST_TIMEZONE);
    }

    function legGo()
    {
        date_default_timezone_set($this->userTimezone);
    }

    function it_converts()
    {
        $fields = [
            'code'          => 'mycode',
            'localized'     => 0,
            'description'   => 'My awesome description',
            'tags'          => 'dog,flowers',
            'categories'    => 'cat1,cat2,cat3',
            'end_of_use'    => '2018-02-01',
        ];

        $this->convert($fields)->shouldReturn([
            'tags'        => ['dog', 'flowers'],
            'categories'  => ['cat1', 'cat2', 'cat3'],
            'code'        => 'mycode',
            'localizable' => false,
            'description' => 'My awesome description',
            'end_of_use'  => '2018-02-01T00:00:00+01:00',
        ]);
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))->during(
            'convert',
            [['not_a_code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_localizable_is_not_in_array()
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

    function it_throws_an_exception_if_required_field_localizable_does_not_contain_valid_value()
    {
        $fields = [
            'code'        => 'mycode',
            'localized'   => 'y',
            'description' => 'My awesome description',
            'tags'        => 'dog,flowers',
            'end_of_use'  => '2018-02-01',
        ];

        $this->shouldThrow(
            new DataArrayConversionException('Localized field contains invalid data only "0" or "1" is accepted')
        )->during('convert',[$fields]);
    }
}
