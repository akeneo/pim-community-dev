<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier as ProductIdentifierValueObject;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\IsUserOwnerOfTheProduct;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\IsUserOwnerOfTheProductValidator;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsUserOwnerOfTheProductValidatorSpec extends ObjectBehavior
{
    private UuidInterface $uuidUnknown;
    private UuidInterface $uuidWithoutCategory;
    private UuidInterface $uuidWithCategory;

    function let(
        GetCategoryCodes $getCategoryCodes,
        GetOwnedCategories $getOwnedCategories,
        GetProductUuids $getProductUuids,
        ExecutionContext $context
    ) {
        $this->uuidUnknown = Uuid::uuid4();
        $this->uuidWithoutCategory = Uuid::uuid4();
        $this->uuidWithCategory = Uuid::uuid4();

        $getProductUuids->fromIdentifier('unknown')->willReturn($this->uuidUnknown);
        $getProductUuids->fromIdentifier('product_without_category')->willReturn($this->uuidWithoutCategory);
        $getProductUuids->fromIdentifier('product_with_category')->willReturn($this->uuidWithCategory);

        $getCategoryCodes->fromProductUuids([$this->uuidUnknown])->willReturn([]);
        $getCategoryCodes->fromProductUuids([$this->uuidWithoutCategory])->willReturn([$this->uuidWithoutCategory->toString() => []]);
        $getCategoryCodes->fromProductUuids([$this->uuidWithCategory])->willReturn([$this->uuidWithCategory->toString() => ['master', 'print']]);

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
        $command = new UpsertProductCommand(1, 'foo');
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$command, new Type([])]);
    }

    function it_throws_an_exception_with_a_wrong_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new \stdClass(), new IsUserOwnerOfTheProduct([])]);
    }

    function it_does_nothing_when_product_does_not_exist(ExecutionContext $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        // with identifier as string
        $command = new UpsertProductCommand(1, 'unknown');
        $this->validate($command, new IsUserOwnerOfTheProduct());

        // with product identifier
        $command = new UpsertProductCommand(1, ProductIdentifierValueObject::fromAttributeCodeAndIdentifier('sku', 'unknown'));
        $this->validate($command, new IsUserOwnerOfTheProduct());

        // with uuid
        $command = new UpsertProductCommand(1,  ProductUuid::fromUuid($this->uuidUnknown));
        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_validates_when_the_product_does_not_have_any_category(ExecutionContext $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        // with identifier as string
        $command = new UpsertProductCommand(1, 'product_without_category');
        $this->validate($command, new IsUserOwnerOfTheProduct());

        // with product identifier
        $command = new UpsertProductCommand(1, ProductIdentifierValueObject::fromAttributeCodeAndIdentifier('sku','product_without_category'));
        $this->validate($command, new IsUserOwnerOfTheProduct());

        // with uuid
        $command = new UpsertProductCommand(1, ProductUuid::fromUuid($this->uuidWithoutCategory));
        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_validates_when_the_product_has_owned_category(
        GetOwnedCategories $getOwnedCategories,
        ExecutionContext $context
    ) {
        $getOwnedCategories->forUserId(['master', 'print'], 1)->willReturn(['master']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        // with identifier as string
        $command = new UpsertProductCommand(1, 'product_with_category');
        $this->validate($command, new IsUserOwnerOfTheProduct());

        // with product identifier
        $command = new UpsertProductCommand(1, ProductIdentifierValueObject::fromAttributeCodeAndIdentifier('sku', 'product_with_category'));
        $this->validate($command, new IsUserOwnerOfTheProduct());

        // with uuid
        $command = new UpsertProductCommand(1, ProductUuid::fromUuid($this->uuidWithCategory));
        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_adds_a_violation_when_the_product_does_not_have_owned_category(
        GetOwnedCategories $getOwnedCategories,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new IsUserOwnerOfTheProduct();

        $getOwnedCategories->forUserId(['master', 'print'], 1)->willReturn([]);
        $context->buildViolation($constraint->message)->shouldBeCalledTimes(3)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('userId')->shouldBeCalledTimes(3)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode('3')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledTimes(3);

        // with identifier as string
        $command = new UpsertProductCommand(1, 'product_with_category');
        $this->validate($command, $constraint);

        // with product identifier
        $command = new UpsertProductCommand(1, ProductIdentifierValueObject::fromAttributeCodeAndIdentifier('sku', 'product_with_category'));
        $this->validate($command, $constraint);

        // with uuid
        $command = new UpsertProductCommand(1, ProductUuid::fromUuid($this->uuidWithCategory));
        $this->validate($command, $constraint);
    }
}
