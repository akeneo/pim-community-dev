<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FreeTextShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FreeTextShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FreeTextShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FreeTextShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'free_text', 'string' => 'abcdef'], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new FreeTextShouldBeValid());
    }

    public function it_should_not_validate_a_property_which_is_not_a_free_text(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(['type' => 'auto_number', 'numberMin' => 2, 'digitsMin' => 3], new FreeTextShouldBeValid());
    }

    public function it_should_build_violation_when_free_text_is_invalid(ExecutionContext $context): void
    {
        $freeTextWithoutString = [
            'type' => 'free_text',
        ];

        $context->buildViolation(
            'validation.identifier_generator.free_text_string_field_required'
        )->shouldBeCalled();

        $this->validate($freeTextWithoutString, new FreeTextShouldBeValid());
    }

    public function it_should_not_build_violation_when_free_text_is_valid(ExecutionContext $context): void
    {
        $freeTextWithoutString = [
            'type' => 'free_text',
            'string' => 'abcdef',
        ];

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($freeTextWithoutString, new FreeTextShouldBeValid());
    }
}
