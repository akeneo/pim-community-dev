<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Converter;

use Akeneo\Category\Infrastructure\Exception\ContentArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;

class FieldsRequirementCheckerSpec extends ObjectBehavior
{
    public function it_does_not_raise_exception_when_there_is_no_required_fields(): void
    {
        $this
            ->shouldNotThrow(StructureArrayConversionException::class)
            ->during('checkFieldsExist', [['code' => 'socks'], []]);
    }

    public function it_does_not_raise_exception_when_all_required_fields_are_present(): void
    {
        $this
            ->shouldNotThrow(StructureArrayConversionException::class)
            ->during('checkFieldsExist', [['code' => 'socks'], ['code']]);
    }

    public function it_should_raise_exception_when_a_required_field_is_not_present(): void
    {
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->during('checkFieldsExist', [['parent' => null], ['code']]);
    }

    public function it_does_not_raise_exception_when_an_empty_field_can_be_empty(): void
    {
        $this
            ->shouldNotThrow(ContentArrayConversionException::class)
            ->during('checkFieldsNotEmpty', [['code' => ''], []]);
    }

    public function it_does_not_raise_exception_when_a_non_empty_field_cannot_be_empty(): void
    {
        $this
            ->shouldNotThrow(ContentArrayConversionException::class)
            ->during('checkFieldsNotEmpty', [['code' => 'socks'], ['code']]);
    }

    public function it_should_raise_exception_when_a_required_field_is_empty(): void
    {
        $this
            ->shouldThrow(ContentArrayConversionException::class)
            ->during('checkFieldsNotEmpty', [['code' => ''], ['code']]);
    }

    public function it_should_raise_exception_when_a_required_field_is_null(): void
    {
        $this
            ->shouldThrow(ContentArrayConversionException::class)
            ->during('checkFieldsNotEmpty', [['code' => null], ['code']]);
    }

    public function it_does_not_raise_exception_when_a_parent_category_code_is_different_from_the_category_code(): void
    {
        $this
            ->shouldNotThrow(StructureArrayConversionException::class)
            ->during('checkParentAutoReference', ['socks', 'shoes']);
    }

    public function it_should_raise_an_exception_when_a_parent_category_code_is_identical_to_the_category_code(): void
    {
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->during('checkParentAutoReference', ['socks', 'socks']);
    }


}
