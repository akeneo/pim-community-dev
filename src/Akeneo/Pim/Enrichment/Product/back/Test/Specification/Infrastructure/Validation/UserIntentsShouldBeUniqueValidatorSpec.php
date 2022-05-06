<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserIntentsShouldBeUnique;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserIntentsShouldBeUniqueValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserIntentsShouldBeUniqueValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserIntentsShouldBeUniqueValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [1, new Type([])]);
    }

    function it_does_nothing_when_the_value_intents_are_distinct(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate([
            new SetTextValue('a_text', 'a_channel', 'a_locale', 'foo'),
            new SetTextValue('a_text', 'a_channel', 'another_locale', 'bar'),
            new SetTextValue('a_text', 'another_channel', 'a_locale', 'baz'),
            new SetTextValue('a_text', 'another_channel', 'another_locale', 'toto'),
        ], new UserIntentsShouldBeUnique());
    }

    function it_throws_an_exception_when_value_intents_collide(ConstraintViolationBuilderInterface $violationBuilder, ExecutionContext $context)
    {
        $constraint = new UserIntentsShouldBeUnique();
        $context->buildViolation($constraint->message, ['{{ attributeCode }}' => 'a_text'])->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate([
            new SetTextValue('a_text', 'a_channel', 'a_locale', 'foo'),
            new SetTextValue('another_text', 'a_channel', 'a_locale', 'bar'),
            new SetTextValue('a_text', 'a_channel', 'a_locale', 'baz'),
            new SetTextValue('a_text', null, null, 'toto'),
        ], $constraint);
    }
}
