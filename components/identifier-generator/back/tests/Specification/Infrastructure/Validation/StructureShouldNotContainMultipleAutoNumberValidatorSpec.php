<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\StructureShouldNotContainMultipleAutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\StructureShouldNotContainMultipleAutoNumberValidator;
use PhpSpec\ObjectBehavior;
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

    public function it_is_initializable()
    {
        $this->shouldHaveType(StructureShouldNotContainMultipleAutoNumberValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [[], new NotBlank()]);
    }

    public function it_should_build_violation_when_auto_number_already_exist(ExecutionContext $context): void
    {
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 2],
            ['type' => 'auto_number', 'numberMin' => 5, 'digitsMin' => 4],
        ];

        $context->buildViolation(
            'validation.create.auto_number_limit_reached',
            ['{{limit}}' => 1]
        )->shouldBeCalled();

        $this->validate($structure, new StructureShouldNotContainMultipleAutoNumber());
    }

    public function it_should_be_valid_when_auto_number_is_under_limit(ExecutionContext $context): void
    {
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 2],
        ];

        $context->buildViolation(
            'validation.create.auto_number_limit_reached',
            ['{{limit}}' => 2]
        )->shouldNotBeCalled();

        $this->validate($structure, new StructureShouldNotContainMultipleAutoNumber());
    }
}
