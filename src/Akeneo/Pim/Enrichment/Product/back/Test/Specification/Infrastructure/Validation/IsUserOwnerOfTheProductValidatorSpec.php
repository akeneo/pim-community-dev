<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\IsUserOwnerOfTheProduct;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\IsUserOwnerOfTheProductValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsUserOwnerOfTheProductValidatorSpec extends ObjectBehavior
{
    function let(
        GetCategoryCodes $getCategoryCodes,
        GetOwnedCategories $getOwnedCategories,
        GetProductUuids $getProductUuids,
        ExecutionContext $context
    ) {
        $this->beConstructedWith($getCategoryCodes, $getOwnedCategories, $getProductUuids);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(IsUserOwnerOfTheProductValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: []
        );
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$command, new Type([])]);
    }

    function it_throws_an_exception_with_a_wrong_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new \stdClass(), new IsUserOwnerOfTheProduct([])]);
    }

    function it_does_nothing_when_product_does_not_exist(
        ExecutionContext $context,
        GetProductUuids $getProductUuids,
    ) {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('unknown'),
            userIntents: []
        );
        $getProductUuids->fromIdentifier('unknown')->shouldBeCalled()->willReturn(null);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_validates_when_the_product_does_not_have_any_category(
        ExecutionContext $context,
        GetProductUuids $getProductUuids,
        GetCategoryCodes $getCategoryCodes,
    ) {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('product_without_category'),
            userIntents: []
        );
        $uuid = Uuid::uuid4();
        $getProductUuids->fromIdentifier('product_without_category')->shouldBeCalled()->willReturn($uuid);
        $getCategoryCodes->fromProductUuids([$uuid])->shouldBeCalled()->willReturn([$uuid->toString() => []]);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_validates_when_the_product_has_owned_category(
        GetOwnedCategories $getOwnedCategories,
        GetProductUuids $getProductUuids,
        GetCategoryCodes $getCategoryCodes,
        ExecutionContext $context
    ) {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('product_with_category'),
            userIntents: []
        );
        $uuid = Uuid::uuid4();
        $getProductUuids->fromIdentifier('product_with_category')->shouldBeCalled()->willReturn($uuid);
        $getCategoryCodes->fromProductUuids([$uuid])->shouldBeCalled()->willReturn([$uuid->toString() => ['master', 'print']]);

        $getOwnedCategories->forUserId(['master', 'print'], 1)->willReturn(['master']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_adds_a_violation_when_the_product_does_not_have_owned_category(
        GetOwnedCategories $getOwnedCategories,
        GetCategoryCodes $getCategoryCodes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
    ) {
        $constraint = new IsUserOwnerOfTheProduct();

        $uuid = Uuid::uuid4();
        $command = UpsertProductCommand::createWithUuid(
            userId: 1,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        );

        $getCategoryCodes->fromProductUuids([$uuid])->shouldBeCalled()->willReturn([$uuid->toString() => ['master', 'print']]);
        $getOwnedCategories->forUserId(['master', 'print'], 1)->willReturn([]);
        $context->buildViolation($constraint->message)->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('userId')->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode('3')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($command, $constraint);
    }
}
