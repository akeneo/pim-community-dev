<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\ShouldStayOwnerOfTheProduct;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\ShouldStayOwnerOfTheProductValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;
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

    function it_only_validates_the_right_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
             ->during('validate', [new SetCategories(['foo']), new NotBlank()]);
    }

    function it_only_validates_category_user_intents()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [new SetGroups(['foo']), new ShouldStayOwnerOfTheProduct()]
        );
    }

    function it_does_not_check_add_categories_user_intents(
        GetProductUuids $getProductUuids,
        ExecutionContext $context
    ) {
        $getProductUuids->fromIdentifier(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(new AddCategories(['bar', 'baz']), new ShouldStayOwnerOfTheProduct());
    }

    function it_does_nothing_when_the_product_is_being_created(
        GetProductUuids $getProductUuids,
        GetCategoryCodes $getCategoryCodes,
        ExecutionContext $context
    ) {
        $productUuid = Uuid::uuid4();
        $context->getRoot()->shouldBeCalled()->willReturn(
            UpsertProductCommand::createWithUuid(42, ProductUuid::fromUuid($productUuid), [])
        );
        $getProductUuids->fromUuid($productUuid)->shouldBeCalled()->willReturn(null);

        $getCategoryCodes->fromProductUuids(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());
    }

    function it_does_nothing_when_the_user_is_not_already_owner_of_the_product(
        GetOwnedCategories $getOwnedCategories,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        GetCategoryCodes $getCategoryCodes,
        GetProductUuids $getProductUuids,
        ExecutionContext $context,
    ) {
        $productUuid = Uuid::uuid4();
        $context->getRoot()->shouldBeCalled()->willReturn(
            UpsertProductCommand::createWithUuid(
                42,
                ProductUuid::fromUuid($productUuid),
                []
            )
        );
        $getProductUuids->fromUuid($productUuid)->shouldBeCalled()->willReturn($productUuid);
        $getCategoryCodes->fromProductUuids([$productUuid])->shouldBeCalled()
            ->willReturn([$productUuid->toString() => ['foo']]);
        $getOwnedCategories->forUserId(['foo'], 42)->shouldBeCalled()->willReturn([]);

        $getNonViewableCategoryCodes->fromProductUuids(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());
    }

    function it_does_not_raise_any_violation_when_the_user_leaves_an_owned_category(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        GetProductUuids $getProductUuids,
        ExecutionContext $context
    ) {
        $uuid = Uuid::uuid4();
        $context->getRoot()->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 10,
            productIdentifier: ProductIdentifier::fromIdentifier('my_sku'),
            userIntents: []
        ));
        $getProductUuids->fromIdentifier('my_sku')->shouldBeCalled()->willReturn($uuid);

        $getCategoryCodes->fromProductUuids([$uuid])->shouldBeCalled()
             ->willReturn([$uuid->toString() => ['categoryA', 'categoryB']]);
        $getOwnedCategories->forUserId(['categoryA', 'categoryB'], 10)->shouldBeCalled()->willReturn(['categoryA', 'categoryB']);

        $getOwnedCategories->forUserId(['categoryA', 'categoryC'], Argument::cetera())->shouldNotBeCalled();
        $getNonViewableCategoryCodes->fromProductUuids(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new SetCategories(['categoryA', 'categoryC']), new ShouldStayOwnerOfTheProduct());
    }

    function it_does_not_raise_a_violation_when_the_user_adds_an_owned_category(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        GetProductUuids $getProductUuids,
        ExecutionContext $context
    ) {
        $uuid = Uuid::uuid4();
        $context->getRoot()->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $getProductUuids->fromUuid($uuid)->shouldBeCalled()->willReturn($uuid);
        $getCategoryCodes->fromProductUuids([$uuid])->shouldBeCalled()->willReturn([$uuid->toString() => ['categoryA']]);
        $getOwnedCategories->forUserId(['categoryA'], 10)->shouldBeCalled()->willReturn(['categoryA']);
        $getOwnedCategories->forUserId(['categoryC'], 10)->shouldBeCalled()->willReturn(['categoryC']);

        $getNonViewableCategoryCodes->fromProductUuids(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new SetCategories(['categoryC']), new ShouldStayOwnerOfTheProduct());
    }

    function it_does_not_raise_any_violation_when_the_product_gets_unclassified(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes,
        GetProductUuids $getProductUuids,
        ExecutionContext $context
    ) {
        $uuid = Uuid::uuid4();
        $context->getRoot()->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $getProductUuids->fromUuid($uuid)->shouldBeCalled()->willReturn($uuid);
        $getCategoryCodes->fromProductUuids([$uuid])->shouldBeCalled()->willReturn([$uuid->toString() => ['categoryA', 'categoryB']]);
        $getOwnedCategories->forUserId(['categoryA', 'categoryB'], 10)->shouldBeCalled()->willReturn(['categoryA', 'categoryB']);
        $getNonViewableCategoryCodes->fromProductUuids([$uuid], 10)->shouldBeCalled()->willReturn([$uuid->toString() => []]);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(new RemoveCategories(['categoryA', 'categoryB']), new ShouldStayOwnerOfTheProduct());
    }

    function it_adds_a_violation_when_the_user_replaces_all_owned_categories(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        GetProductUuids $getProductUuids,
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
        $getProductUuids->fromUuid($uuid)->shouldBeCalled()->willReturn($uuid);
        $getCategoryCodes->fromProductUuids([$uuid])->shouldBeCalled()->willReturn(['categoryA', 'categoryB']);
        $getOwnedCategories->forUserId(['categoryA', 'categoryB'], 10)->willReturn(['categoryA']);
        $getOwnedCategories->forUserId(['categoryB', 'categoryC'], 10)->shouldBeCalled()->willReturn([]);

        $context->buildViolation($constraint->message)->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->setCode((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(new SetCategories(['categoryB', 'categoryC']), $constraint);
    }

    function it_adds_a_violation_when_the_user_removes_all_owned_categories(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        GetProductUuids $getProductUuids,
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
        $getProductUuids->fromUuid($uuid)->shouldBeCalled()->willReturn($uuid);
        $getCategoryCodes->fromProductUuids([$uuid])->willReturn([$uuid->toString() => ['categoryA', 'categoryB', 'categoryC']]);
        $getOwnedCategories->forUserId(['categoryA', 'categoryB', 'categoryC'], 10)->willReturn(['categoryB']);
        $getOwnedCategories->forUserId(['categoryA', 'categoryC'], 10)->willReturn([]);

        $context->buildViolation($constraint->message)->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->setCode((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(new RemoveCategories(['categoryB']), $constraint);
    }

    function it_adds_a_violation_when_all_viewable_categories_are_removed(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        GetProductUuids $getProductUuids,
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
        $getProductUuids->fromUuid($uuid)->shouldBeCalled()->willReturn($uuid);
        $getCategoryCodes->fromProductUuids([$uuid])->willReturn([$uuid->toString() => ['categoryA', 'categoryB']]);
        $getOwnedCategories->forUserId(['categoryA', 'categoryB'], 10)->willReturn(['categoryB']);
        $getNonViewableCategoryCodes->fromProductUuids([$uuid], 10)->shouldBeCalled()->willReturn([$uuid->toString() => ['non_viewable_category']]);

        $context->buildViolation($constraint->message)->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->setCode((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(new RemoveCategories(['categoryA', 'categoryB']), $constraint);
    }
}
