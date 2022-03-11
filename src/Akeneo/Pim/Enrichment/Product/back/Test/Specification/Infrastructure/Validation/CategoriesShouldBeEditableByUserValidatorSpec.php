<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\CategoriesShouldBeEditableByUser;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\CategoriesShouldBeEditableByUserValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CategoriesShouldBeEditableByUserValidatorSpec extends ObjectBehavior
{
    function let(
        GetOwnedCategories    $getOwnedCategories,
        GetViewableCategories $getViewableCategories,
        ExecutionContext      $context
    ) {
        $this->beConstructedWith($getOwnedCategories, $getViewableCategories);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(CategoriesShouldBeEditableByUserValidator::class);
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
            ->during('validate', [new \stdClass(), new CategoriesShouldBeEditableByUser([])]);
    }

    function it_allows_adding_categories_if_user_has_access(
        ExecutionContext $context,
        GetOwnedCategories $getOwnedCategories,
        GetViewableCategories $getViewableCategories
    ) {
        $categoryUserIntent = new SetCategories(['master', 'print', 'ecommerce']);

        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'foo',
            categoryUserIntent: $categoryUserIntent
        ));
        $getOwnedCategories->forUserId(['master', 'print', 'ecommerce'], 1)->willReturn(['print']);
        $getViewableCategories->forUserId(['master', 'print', 'ecommerce'], 1)->willReturn(['master', 'print', 'ecommerce']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($categoryUserIntent, new CategoriesShouldBeEditableByUser());
    }

    function it_adds_a_violation_when_adding_a_category_with_no_own_access(
        ExecutionContext                    $context,
        GetOwnedCategories                  $getOwnedCategories,
        GetViewableCategories               $getViewableCategories,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $categoryUserIntent = new SetCategories(['master']);

        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'foo',
            categoryUserIntent: $categoryUserIntent
        ));
        $getOwnedCategories->forUserId(['master'], 1)->willReturn([]);
        $getViewableCategories->forUserId(['master'], 1)->willReturn(['master']);

        $context->buildViolation('pim_enrich.product.validation.upsert.no_own_access_on_category')->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($categoryUserIntent, new CategoriesShouldBeEditableByUser());
    }

    function it_throws_an_exception_if_the_user_cannot_read_all_categories(
        ExecutionContext $context,
        GetOwnedCategories $getOwnedCategories,
        GetViewableCategories $getViewableCategories,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $categoryUserIntent = new SetCategories(['master', 'print']);

        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'foo',
            categoryUserIntent: $categoryUserIntent
        ));
        $getOwnedCategories->forUserId(['master', 'print'], 1)->willReturn(['master']);
        $getViewableCategories->forUserId(['master', 'print'], 1)->willReturn(['master']);

        $context->buildViolation('pim_enrich.product.validation.upsert.no_view_access_on_category')->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($categoryUserIntent, new CategoriesShouldBeEditableByUser());
    }
}
