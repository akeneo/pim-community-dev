<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\ShouldStayOwnerOfTheProduct;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\ShouldStayOwnerOfTheProductValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ShouldStayOwnerOfTheProductValidatorSpec extends ObjectBehavior
{
    function let(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        GetCategoryCodes $getCategoryCodes,
        GetProductUuids $getProductUuids,
        ExecutionContext $context,
    ) {
        $this->beConstructedWith($getOwnedCategories, $getNonViewableCategoryCodes, $getCategoryCodes, $getProductUuids);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ShouldStayOwnerOfTheProductValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_gets_product_uuid_from_identifier(
        ExecutionContext $context,
        GetProductUuids $getProductUuids,
    ) {
        $context->getRoot()->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 10,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: []
        ));
        $getProductUuids->fromIdentifier('foo')->shouldBeCalled()->willReturn(null);

        $this->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());
    }

    function it_validates_when_the_user_stays_owner_on_categories(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        ExecutionContext $context
    ) {
        $uuid = Uuid::uuid4();
        $context->getRoot()->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $getNonViewableCategoryCodes->fromProductUuids([$uuid], 10)
            ->shouldBeCalled()
            ->willReturn([$uuid->toString() => ['categoryA']])
        ;
        $getOwnedCategories->forUserId(['categoryB', 'categoryA'], 10)->willReturn(['categoryB']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new SetCategories(['categoryB']), new ShouldStayOwnerOfTheProduct());
    }

    function it_validates_when_the_product_becomes_uncategorized(
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        ExecutionContext $context
    ) {

        $uuid = Uuid::uuid4();
        $context->getRoot()->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $getNonViewableCategoryCodes->fromProductUuids([$uuid], 10)
            ->willReturn([$uuid->toString() => []])
        ;
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());
    }

    function it_adds_a_violation_when_the_user_does_not_stay_owner_on_categories_with_set_categories(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ShouldStayOwnerOfTheProduct();
        $uuid = Uuid::uuid4();
        $context->getRoot()->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $getNonViewableCategoryCodes->fromProductUuids([$uuid], 10)
            ->willReturn([$uuid->toString() => ['categoryA']])
        ;
        $getOwnedCategories->forUserId(['categoryB', 'categoryA'], 10)->willReturn([]);
        $context->buildViolation($constraint->message)->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->setCode((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(new SetCategories(['categoryB']), $constraint);
    }

    function it_adds_a_violation_when_the_user_does_not_stay_owner_on_categories_by_removing_categories(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ShouldStayOwnerOfTheProduct();
        $uuid = Uuid::uuid4();
        $context->getRoot()->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $getCategoryCodes->fromProductUuids([$uuid])
            ->willReturn([$uuid->toString() => ['categoryA', 'categoryB', 'categoryC']])
        ;
        $getOwnedCategories->forUserId(['categoryA', 'categoryC'], 10)->willReturn([]);
        $context->buildViolation($constraint->message)->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->setCode((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(new RemoveCategories(['categoryB']), $constraint);
    }

    function it_does_nothing_when_the_user_intent_is_to_add_categories(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new AddCategories(['categoryB']), new ShouldStayOwnerOfTheProduct());
    }
}
