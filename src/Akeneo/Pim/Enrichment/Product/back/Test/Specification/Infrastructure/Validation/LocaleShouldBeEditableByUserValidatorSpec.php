<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\Locale\API\Query\IsLocaleEditable;
use Akeneo\Channel\Locale\API\Query\IsLocaleEditableQuery;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Domain\QueryBus;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleShouldBeEditableByUser;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleShouldBeEditableByUserValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleShouldBeEditableByUserValidatorSpec extends ObjectBehavior
{
    function let(QueryBus $queryBus, ExecutionContext $context)
    {
        $this->beConstructedWith($queryBus);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleShouldBeEditableByUserValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'foo');

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$command, new Type([])]);
    }

    function it_throws_an_exception_with_a_wrong_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new \stdClass(), new LocaleShouldBeEditableByUser([])]);
    }

    function it_validates_when_the_locale_is_editable_by_the_user(
        ExecutionContext $context,
        QueryBus $queryBus
    ) {
        $valueUserIntent = new SetTextValue('a_text', null, 'en_US', 'new value');

        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'foo',
            valueUserIntents: [$valueUserIntent]
        ));
        $queryBus->execute(new IsLocaleEditableQuery('en_US', 1))->willReturn(true);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($valueUserIntent, new LocaleShouldBeEditableByUser());
    }

    function it_adds_a_violation_when_the_locale_is_not_editable_for_the_user(
        ExecutionContext $context,
        QueryBus $queryBus,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new LocaleShouldBeEditableByUser();
        $valueUserIntent = new SetTextValue('a_text', null, 'de_DE', 'new value');

        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'foo',
            valueUserIntents: [$valueUserIntent]
        ));
        $queryBus->execute(new IsLocaleEditableQuery('de_DE', 1))->willReturn(false);
        $context->buildViolation($constraint->message, ['{{ locale_code }}' => 'de_DE'])
            ->shouldBeCalledOnce()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($valueUserIntent, new LocaleShouldBeEditableByUser());
    }

    function it_does_nothing_when_value_intent_does_not_concern_a_locale(
        ExecutionContext $context,
        QueryBus $queryBus
    ) {
        $valueUserIntent = new SetTextValue('a_text', null, null, 'new value');

        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'foo',
            valueUserIntents: [$valueUserIntent]
        ));
        $queryBus->execute(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($valueUserIntent, new LocaleShouldBeEditableByUser());
    }
}
