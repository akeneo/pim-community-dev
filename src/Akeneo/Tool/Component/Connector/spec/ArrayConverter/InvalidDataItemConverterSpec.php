<?php

namespace spec\Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use PhpSpec\ObjectBehavior;

class InvalidDataItemConverterSpec extends ObjectBehavior
{
    function it_can_not_convert_because_of_multi_dimensional_array()
    {
        $item = [
            'string_key' => 'effeacef4848484',
            'array_key' => [
                'short' => ['foo'],
                'long' => ['foobar'],
            ],
        ];

        $this->shouldThrow(DataArrayConversionException::class)->during('convert', [$item]);
    }

    function it_can_not_convert_because_of_object()
    {
        $item = [
            'string_key' => 'effeacef4848484',
            'object_key' => new \stdClass(),
        ];

        $this->shouldThrow(DataArrayConversionException::class)->during('convert', [$item]);
    }

    function it_converts_to_a_string_array()
    {
        $myObject = new FakeObject();
        $myObject->property = 'objectValue';

        $item = [
            'string_key' => 'effeacef4848484',
            'array_key' => ['short' => 'foo', 'long' => 'foobar'],
            'date_key' => new \DateTime('2019-08-29'),
            'numeric_key' => 666,
            'null_key' => null,
            'object_key' => $myObject,
        ];

        $convertedItem = [
            'string_key' => 'effeacef4848484',
            'array_key' => 'foo,foobar',
            'date_key' => '2019-08-29',
            'numeric_key' => '666',
            'null_key' => '',
            'object_key' => 'objectValue',
        ];

        $this->convert($item)->shouldReturn($convertedItem);
    }
}

class FakeObject
{
    /** @var string */
    public $property;

    public function __toString(): string
    {
        return $this->property;
    }
}
