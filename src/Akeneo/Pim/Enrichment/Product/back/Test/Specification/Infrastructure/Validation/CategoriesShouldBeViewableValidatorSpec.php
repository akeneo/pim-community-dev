<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\CategoriesShouldBeViewable;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\CategoriesShouldBeViewableValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CategoriesShouldBeViewableValidatorSpec extends ObjectBehavior
{
    function let(GetViewableCategories $getViewableCategories, ExecutionContext $context)
    {
        $this->beConstructedWith($getViewableCategories);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(CategoriesShouldBeViewableValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: 1, productIdentifier: ProductIdentifier::fromIdentifier('foo'), userIntents: []);

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$command, new Type([])]);
    }

    function it_throws_an_exception_with_a_wrong_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new \stdClass(), new CategoriesShouldBeViewable([])]);
    }

    function it_allows_adding_categories_if_user_has_access(
        ExecutionContext $context,
        GetViewableCategories $getViewableCategories
    ) {
        $categoryUserIntent = new SetCategories(['master', 'print', 'ecommerce']);

        $context->getRoot()->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$categoryUserIntent]
        ));
        $getViewableCategories->forUserId(['master', 'print', 'ecommerce'], 1)->willReturn(['master', 'print', 'ecommerce']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($categoryUserIntent, new CategoriesShouldBeViewable());
    }

    function it_adds_a_violation_when_a_category_is_not_viewable(
        ExecutionContext $context,
        GetViewableCategories $getViewableCategories,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $categoryUserIntent = new SetCategories(['master', 'print', 'ecommerce']);

        $context->getRoot()->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$categoryUserIntent]
        ));
        $getViewableCategories->forUserId(['master', 'print', 'ecommerce'], 1)->willReturn(['master', 'ecommerce']);

        $context->buildViolation(
            'pim_enrich.product.validation.upsert.category_does_not_exist',
            ['{{ categoryCodes }}' => 'print', '%count%' => 1]
        )->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($categoryUserIntent, new CategoriesShouldBeViewable());
    }

    function it_adds_a_violation_when_several_categories_are_not_viewable(
        ExecutionContext $context,
        GetViewableCategories $getViewableCategories,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $categoryUserIntent = new SetCategories(['master', 'print', 'ecommerce', 'print']);

        $context->getRoot()->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$categoryUserIntent]
        ));
        $getViewableCategories->forUserId(['master', 'print', 'ecommerce'], 1)->willReturn(['master']);

        $context->buildViolation(
            'pim_enrich.product.validation.upsert.category_does_not_exist',
            ['{{ categoryCodes }}' => 'print, ecommerce', '%count%' => 2]
        )->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($categoryUserIntent, new CategoriesShouldBeViewable());
    }
}
