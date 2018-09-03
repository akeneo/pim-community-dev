<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class InvalidPropertyTypeExceptionSpec extends ObjectBehavior
{
    function it_creates_a_not_scalar_exception()
    {
        $exception = InvalidPropertyTypeException::scalarExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            []
        );

        $this->beConstructedWith(
            'attribute',
            [],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects a scalar as data, "array" given.',
            InvalidPropertyTypeException::SCALAR_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_boolean_exception()
    {
        $exception = InvalidPropertyTypeException::booleanExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            []
        );

        $this->beConstructedWith(
            'attribute',
            [],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects a boolean as data, "array" given.',
            InvalidPropertyTypeException::BOOLEAN_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_float_exception()
    {
        $exception = InvalidPropertyTypeException::floatExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            []
        );

        $this->beConstructedWith(
            'attribute',
            [],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects a float as data, "array" given.',
            InvalidPropertyTypeException::FLOAT_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_integer_exception()
    {
        $exception = InvalidPropertyTypeException::integerExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            []
        );

        $this->beConstructedWith(
            'attribute',
            [],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects an integer as data, "array" given.',
            InvalidPropertyTypeException::INTEGER_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_numeric_exception()
    {
        $exception = InvalidPropertyTypeException::numericExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            []
        );

        $this->beConstructedWith(
            'attribute',
            [],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects a numeric as data, "array" given.',
            InvalidPropertyTypeException::NUMERIC_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_string_exception()
    {
        $exception = InvalidPropertyTypeException::stringExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            []
        );

        $this->beConstructedWith(
            'attribute',
            [],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects a string as data, "array" given.',
            InvalidPropertyTypeException::STRING_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_array_exception()
    {
        $exception = InvalidPropertyTypeException::arrayExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            true
        );

        $this->beConstructedWith(
            'attribute',
            true,
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects an array as data, "boolean" given.',
            InvalidPropertyTypeException::ARRAY_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_bad_array_structure_exception()
    {
        $exception = InvalidPropertyTypeException::validArrayStructureExpected(
            'attribute',
            'one of the attribute code is no a scalar, "array" given',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            [[]]
        );

        $this->beConstructedWith(
            'attribute',
            [[]],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects an array with valid data, one of the attribute code is no a scalar, "array" given.',
            InvalidPropertyTypeException::VALID_ARRAY_STRUCTURE_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_array_of_arrays_structure_exception()
    {
        $exception = InvalidPropertyTypeException::arrayOfArraysExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            ['string']
        );

        $this->beConstructedWith(
            'attribute',
            ['string'],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects an array of arrays as data.',
            InvalidPropertyTypeException::ARRAY_OF_ARRAYS_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_array_of_objects_structure_exception()
    {
        $exception = InvalidPropertyTypeException::arrayOfObjectsExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            ['string']
        );

        $this->beConstructedWith(
            'attribute',
            ['string'],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects an array of objects as data.',
            InvalidPropertyTypeException::ARRAY_OF_OBJECTS_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_a_not_key_not_found_exception()
    {
        $exception = InvalidPropertyTypeException::arrayKeyExpected(
            'attribute',
            'currency',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            []
        );

        $this->beConstructedWith(
            'attribute',
            [],
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects an array with the key "currency".',
            InvalidPropertyTypeException::ARRAY_KEY_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }
}
