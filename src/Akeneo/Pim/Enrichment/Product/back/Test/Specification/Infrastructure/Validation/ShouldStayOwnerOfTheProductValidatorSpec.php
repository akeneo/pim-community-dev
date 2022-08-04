<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier as ProductIdentifierValueObject;
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
        ExecutionContext $context
    ) {
        $this->beConstructedWith($getOwnedCategories, $getNonViewableCategoryCodes, $getCategoryCodes, $getProductUuids);
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
        ExecutionContext $context,
        GetProductUuids $getProductUuids,
    ) {
        $getOwnedCategories->forUserId(['categoryB', 'categoryA'], 10)->willReturn(['categoryB']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();
        $uuid = Uuid::uuid4();
        $getProductUuids->fromIdentifier('foo')->shouldBeCalledTimes(2)->willReturn($uuid);
        $getNonViewableCategoryCodes->fromProductUuids([$uuid], 10)->willReturn([$uuid->toString() => ['categoryA']]);

        // with identifier as string
        $context->getRoot()->willReturn(new UpsertProductCommand(10, 'foo'));
        $this->validate(new SetCategories(['categoryB']), new ShouldStayOwnerOfTheProduct());

        // with product identifier
        $context->getRoot()->willReturn(new UpsertProductCommand(10, ProductIdentifierValueObject::fromAttributeCodeAndIdentifier('sku', 'foo')));
        $this->validate(new SetCategories(['categoryB']), new ShouldStayOwnerOfTheProduct());

        //with product uuid
        $context->getRoot()->willReturn(new UpsertProductCommand(10, ProductUuid::fromUuid($uuid)));
        $this->validate(new SetCategories(['categoryB']), new ShouldStayOwnerOfTheProduct());
    }

    function it_validates_when_the_product_becomes_uncategorized(
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        GetProductUuids $getProductUuids,
        ExecutionContext $context
    ) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();
        $uuid = Uuid::uuid4();
        $getProductUuids->fromIdentifier('foo')->shouldBeCalledTimes(2)->willReturn($uuid);
        $getNonViewableCategoryCodes->fromProductUuids([$uuid], 10)->willReturn([$uuid->toString() => []]);

        // with identifier as string
        $context->getRoot()->willReturn(new UpsertProductCommand(10, 'foo'));
        $this->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());

        // with identifier as string
        $context->getRoot()->willReturn(new UpsertProductCommand(10, ProductIdentifierValueObject::fromAttributeCodeAndIdentifier('sku', 'foo')));
        $this->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());

        // with product uuid
        $context->getRoot()->willReturn(new UpsertProductCommand(10, ProductUuid::fromUuid($uuid)));
        $this->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());
    }

    function it_adds_a_violation_when_the_user_does_not_stay_owner_on_categories_with_set_categories(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        GetProductUuids $getProductUuids,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ShouldStayOwnerOfTheProduct();
        $uuid = Uuid::uuid4();
        $getProductUuids->fromIdentifier('foo')->shouldBeCalledTimes(2)->willReturn($uuid);
        $getNonViewableCategoryCodes->fromProductUuids([$uuid], 10)->willReturn([$uuid->toString() => ['categoryA']]);
        $getOwnedCategories->forUserId(['categoryB', 'categoryA'], 10)->willReturn([]);
        $context->buildViolation($constraint->message)->shouldBeCalledTimes(3)->willReturn($violationBuilder);
        $violationBuilder->setCode((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledTimes(3);

        // with identifier as string
        $context->getRoot()->willReturn(new UpsertProductCommand(10, 'foo'));
        $this->validate(new SetCategories(['categoryB']), $constraint);

        // with product identifier
        $context->getRoot()->willReturn(new UpsertProductCommand(10, ProductIdentifierValueObject::fromAttributeCodeAndIdentifier('sku', 'foo')));
        $this->validate(new SetCategories(['categoryB']), $constraint);

        // with product uuid
        $context->getRoot()->willReturn(new UpsertProductCommand(10, ProductUuid::fromUuid($uuid)));
        $this->validate(new SetCategories(['categoryB']), $constraint);
    }

    function it_adds_a_violation_when_the_user_does_not_stay_owner_on_categories_by_removing_categories(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        GetProductUuids $getProductUuids,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ShouldStayOwnerOfTheProduct();
        $uuid = Uuid::uuid4();
        $getProductUuids->fromIdentifier('foo')->shouldBeCalledTimes(2)->willReturn($uuid);
        $getCategoryCodes->fromProductUuids([$uuid])->willReturn([$uuid->toString() => ['categoryA', 'categoryB', 'categoryC']]);
        $getOwnedCategories->forUserId(['categoryA', 'categoryC'], 10)->willReturn([]);
        $context->buildViolation($constraint->message)->shouldBeCalledTimes(3)->willReturn($violationBuilder);
        $violationBuilder->setCode((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledTimes(3);

        // with identifier as string
        $context->getRoot()->willReturn(new UpsertProductCommand(10, 'foo'));
        $this->validate(new RemoveCategories(['categoryB']), $constraint);

        // with product identifier
        $context->getRoot()->willReturn(new UpsertProductCommand(10, ProductIdentifierValueObject::fromAttributeCodeAndIdentifier('sku', 'foo')));
        $this->validate(new RemoveCategories(['categoryB']), $constraint);

        // with product uuid
        $context->getRoot()->willReturn(new UpsertProductCommand(10, ProductUuid::fromUuid($uuid)));
        $this->validate(new RemoveCategories(['categoryB']), $constraint);
    }

    function it_does_nothing_when_the_user_intent_is_to_add_categories(ExecutionContext $context) {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new AddCategories(['categoryB']), new ShouldStayOwnerOfTheProduct());
    }
}
