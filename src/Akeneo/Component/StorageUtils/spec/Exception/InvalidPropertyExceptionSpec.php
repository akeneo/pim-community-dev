<?php

namespace spec\Akeneo\Component\StorageUtils\Exception;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;

class InvalidPropertyExceptionSpec extends ObjectBehavior
{
    function it_creates_an_empty_value_exception()
    {
        $exception = InvalidPropertyException::valueNotEmptyExpected(
            'attribute',
            'Pim\Component\Catalog\Updater\Attribute'
        );

        $this->beConstructedWith(
            'attribute',
            null,
            'Pim\Component\Catalog\Updater\Attribute',
            'Property "attribute" does not expect an empty value.',
            InvalidPropertyException::NOT_EMPTY_VALUE_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_an_invalid_entity_code_exception()
    {
        $exception = InvalidPropertyException::validEntityCodeExpected(
            'attribute',
            'code',
            'The attribute does not exist',
            'Pim\Component\Catalog\Updater\Attribute',
            'unknown_code'
        );

        $this->beConstructedWith(
            'attribute',
            'unknown_code',
            'Pim\Component\Catalog\Updater\Attribute',
            'Property "attribute" expects a valid code. The attribute does not exist, "unknown_code" given.',
            InvalidPropertyException::VALID_ENTITY_CODE_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_an_invalid_date_exception()
    {
        $exception = InvalidPropertyException::dateExpected(
            'created_date',
            'yyyy-mm-dd',
            'Pim\Component\Catalog\Updater\Setter\DateAttributeSetter',
            '2017/12/12'
        );

        $this->beConstructedWith(
            'created_date',
            '2017/12/12',
            'Pim\Component\Catalog\Updater\Setter\DateAttributeSetter',
            'Property "created_date" expects a string with the format "yyyy-mm-dd" as data, "2017/12/12" given.',
            InvalidPropertyException::DATE_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }

    function it_creates_an_invalid_group_type_exception()
    {
        $exception = InvalidPropertyException::validGroupTypeExpected(
            'group',
            'Group is not valid',
            'Pim\Component\Catalog\Updater\Attribute',
            'variant'
        );

        $this->beConstructedWith(
            'group',
            'variant',
            'Pim\Component\Catalog\Updater\Attribute',
            'Property "group" expects a valid group type. Group is not valid, "variant" given.',
            InvalidPropertyException::VALID_GROUP_TYPE_EXPECTED_CODE
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn($exception->getPropertyName());
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }
}
