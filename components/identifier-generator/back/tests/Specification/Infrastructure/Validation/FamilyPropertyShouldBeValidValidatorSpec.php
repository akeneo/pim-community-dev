<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyPropertyShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyPropertyShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyPropertyShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $globalValidator,
        ExecutionContext $context
    ): void
    {
        $this->beConstructedWith($globalValidator);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FamilyPropertyShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'family', 'process' => ['type' => 'no']], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new FamilyPropertyShouldBeValid());
    }

    public function it_should_not_validate_a_property_which_have_no_type(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(['process' => []], new FamilyPropertyShouldBeValid());
    }

    public function it_should_not_validate_a_property_which_have_bad_type(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(['type' => 'auto_number', 'process' => []], new FamilyPropertyShouldBeValid());
    }

    public function it_should_build_violation_when_process_is_missing(ExecutionContext $context): void
    {
        $context
            ->buildViolation(
                'validation.identifier_generator.family_property_fields_required',
                ['{{ field }}' => 'process']
            )
            ->shouldBeCalledOnce();

        $this->validate(['type' => 'family'], new FamilyPropertyShouldBeValid());
    }

    public function it_should_not_validate_a_property_which_have_no_type_under_process(ExecutionContext $context): void
    {
        $structure = ['type' => 'family', 'process' => []];
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($structure, new FamilyPropertyShouldBeValid());
    }

    public function it_should_validate_a_property_with_a_type_no_process(
        ExecutionContext $context
    ): void
    {
        $process = ['type' => 'no'];
        $structure = ['type' => 'family', 'process' => $process];
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($structure, new FamilyPropertyShouldBeValid());
    }
}
