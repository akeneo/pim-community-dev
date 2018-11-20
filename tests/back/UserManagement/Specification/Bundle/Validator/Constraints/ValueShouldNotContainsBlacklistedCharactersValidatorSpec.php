<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Bundle\Validator\Constraints;

use Akeneo\UserManagement\Bundle\Validator\Constraints\ValueShouldNotContainsBlacklistedCharacters;
use Akeneo\UserManagement\Bundle\Validator\Constraints\ValueShouldNotContainsBlacklistedCharactersValidator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValueShouldNotContainsBlacklistedCharactersValidatorSpec extends ObjectBehavior
{

    function let(
        ExecutionContextInterface $context
    )
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueShouldNotContainsBlacklistedCharactersValidator::class);
    }

    function it_is_a_constraints_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_returns_if_empty_value(
        ValueShouldNotContainsBlacklistedCharacters $constraint
    )
    {
        $this->validate('', $constraint)->shouldReturn(null);
    }

    function it_throws_an_unexpected_type_exception_if_not_a_string(
        ValueShouldNotContainsBlacklistedCharacters $constraint
    )
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate' , [new \StdClass(), $constraint]);
    }

    function it_does_not_add_violation_if_no_blacklisted_characters_are_present_in_the_string_to_validate(
        ValueShouldNotContainsBlacklistedCharacters $constraint
    )
    {
        $this->validate('a pretty healthy string', $constraint)->shouldReturn(null);
    }

    function it_adds_violation_if_blacklisted_characters_are_present_in_the_string_to_validate(
        $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ValueShouldNotContainsBlacklistedCharacters $constraint
    )
    {
        $context->buildViolation('This value should not contains following characters: {{ items }}.')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ items }}', '<, >, &, "')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate('a non <script>alert("busted");</script> healthy string', $constraint);
    }
}
