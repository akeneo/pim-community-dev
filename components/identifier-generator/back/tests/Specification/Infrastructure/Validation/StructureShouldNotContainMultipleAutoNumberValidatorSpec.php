<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\StructureShouldNotContainMultipleAutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\StructureShouldNotContainMultipleAutoNumberValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StructureShouldNotContainMultipleAutoNumberValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(StructureShouldNotContainMultipleAutoNumberValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [[], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new StructureShouldNotContainMultipleAutoNumber());
    }

    public function it_should_not_validate_something_else_than_an_array_of_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate([new \stdClass()], new StructureShouldNotContainMultipleAutoNumber());
    }

    public function it_should_not_validate_something_else_than_an_array_of_property(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate([[]], new StructureShouldNotContainMultipleAutoNumber());
    }

    public function it_should_not_validate_a_structure_without_auto_number(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'free_text', 'string' => 'ghijkl'],
        ];
        $this->validate($structure, new StructureShouldNotContainMultipleAutoNumber());
    }

    public function it_should_build_violation_when_structure_contains_multiple_auto_number(ExecutionContext $context): void
    {
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 2],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 4],
        ];

        $context
            ->buildViolation('validation.identifier_generator.structure_auto_number_limit_reached', [
                '{{limit}}' => 1,
            ])
            ->shouldBeCalled();

        $this->validate($structure, new StructureShouldNotContainMultipleAutoNumber());
    }

    public function it_should_be_valid_when_auto_number_is_under_limit(ExecutionContext $context): void
    {
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 2],
        ];

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($structure, new StructureShouldNotContainMultipleAutoNumber());
    }
}
