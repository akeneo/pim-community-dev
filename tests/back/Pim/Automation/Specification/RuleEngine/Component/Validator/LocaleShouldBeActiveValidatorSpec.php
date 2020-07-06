<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\LocaleShouldBeActive;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\LocaleShouldBeActiveValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleShouldBeActiveValidatorSpec extends ObjectBehavior
{
    function let(ChannelExistsWithLocaleInterface $channelExistsWithLocale, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($channelExistsWithLocale);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(LocaleShouldBeActiveValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new IsNull()]);
    }

    function it_does_not_validate_a_non_string_value(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ) {
        $channelExistsWithLocale->isLocaleActive(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new LocaleShouldBeActive());
    }

    function it_does_not_add_a_violation_if_the_locale_is_active(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ) {
        $channelExistsWithLocale->isLocaleActive('en_US')->shouldBeCalled()->willReturn(true);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('en_US', new LocaleShouldBeActive());
    }

    function it_adds_a_violation_if_the_locale_is_not_active(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new LocaleShouldBeActive();
        $channelExistsWithLocale->isLocaleActive('es_CA')->shouldBeCalled()->willReturn(false);
        $context->buildViolation(
            $constraint->message,
            [
                '{{ locale_code }}' => 'es_CA',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('es_CA', $constraint);
    }
}
