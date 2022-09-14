<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\FieldsRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Infrastructure\Exception\ContentArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;

class FieldsRequirementCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FieldsRequirementChecker::class);
        $this->shouldImplement(RequirementChecker::class);
    }

    public function it_does_not_raise_exception_when_all_required_fields_are_present(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => [
                'en_US' => 'socks'
            ]
        ];

        $this
            ->shouldNotThrow(StructureArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_does_not_raise_exception_on_no_required_label(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => null
        ];

        $this
            ->shouldNotThrow(StructureArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_should_raise_exception_when_a_required_field_is_not_present(): void
    {
        $data = [
            'labels' => [
                'en_US' => 'socks'
            ]
        ];

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_does_not_raise_exception_when_a_code_field_cannot_be_empty(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => null
        ];

        $this
            ->shouldNotThrow(ContentArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_should_raise_exception_when_code_field_is_empty(): void
    {
        $data = [
            'code' => '',
            'labels' => null
        ];

        $this
            ->shouldThrow(ContentArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_should_raise_exception_when_a_required_field_is_null(): void
    {
        $data = [
            'code' => null,
            'labels' => null
        ];

        $this
            ->shouldThrow(ContentArrayConversionException::class)
            ->duringCheck($data);
    }

    public function it_does_not_raise_exception_when_a_parent_category_code_is_different_from_the_category_code(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => null,
            'parent' => 'hat'
        ];

        $this
            ->shouldNotThrow(StructureArrayConversionException::class)
            ->duringCheck($data);
    }
}
