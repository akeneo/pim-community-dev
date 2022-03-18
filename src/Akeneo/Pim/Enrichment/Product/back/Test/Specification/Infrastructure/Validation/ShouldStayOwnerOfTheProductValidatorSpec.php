<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\ShouldStayOwnerOfTheProduct;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\ShouldStayOwnerOfTheProductValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ShouldStayOwnerOfTheProductValidatorSpec extends ObjectBehavior
{
    function let(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        ExecutionContext $context
    ) {
        $this->beConstructedWith($getOwnedCategories, $getNonViewableCategoryCodes);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ShouldStayOwnerOfTheProductValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_validates_when_the_user_stays_owner_on_categories(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        ExecutionContext $context
    ) {
        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 10,
            productIdentifier: 'foo'
        ));
        $getNonViewableCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')], 10)
            ->willReturn(['foo' => ['categoryA']])
        ;
        $getOwnedCategories->forUserId(['categoryB', 'categoryA'], 10)->willReturn(['categoryB']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new SetCategories(['categoryB']), new ShouldStayOwnerOfTheProduct());
    }

    function it_validates_when_the_product_becomes_uncategorized(
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        ExecutionContext $context
    ) {
        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 10,
            productIdentifier: 'foo'
        ));
        $getNonViewableCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')], 10)
            ->willReturn(['foo' => []])
        ;
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());
    }

    function it_adds_a_violation_when_the_user_does_not_stay_owner_on_categories(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ShouldStayOwnerOfTheProduct();
        $context->getRoot()->willReturn(new UpsertProductCommand(
            userId: 10,
            productIdentifier: 'foo'
        ));
        $getNonViewableCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')], 10)
            ->willReturn(['foo' => ['categoryA']])
        ;
        $getOwnedCategories->forUserId(['categoryB', 'categoryA'], 10)->willReturn([]);
        $context->buildViolation($constraint->message)->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(new SetCategories(['categoryB']), $constraint);
    }
}
