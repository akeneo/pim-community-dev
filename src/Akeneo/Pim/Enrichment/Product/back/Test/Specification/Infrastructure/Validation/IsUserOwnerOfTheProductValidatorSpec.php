<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\IsUserOwnerOfTheProduct;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\IsUserOwnerOfTheProductValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsUserOwnerOfTheProductValidatorSpec extends ObjectBehavior
{
    function let(
        GetCategoryCodes $getCategoryCodes,
        GetOwnedCategories $getOwnedCategories,
        ExecutionContext $context
    ) {
        $getCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('unknown')])
            ->willReturn([]);
        $getCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('product_without_category')])
            ->willReturn(['product_without_category' => []]);
        $getCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('product_with_category')])
            ->willReturn(['product_with_category' => ['master', 'print']]);

        $this->beConstructedWith($getCategoryCodes, $getOwnedCategories);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(IsUserOwnerOfTheProductValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $command = new UpsertProductCommand(userId: 1, identifierOrUuid: 'foo');

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$command, new Type([])]);
    }

    function it_throws_an_exception_with_a_wrong_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new \stdClass(), new IsUserOwnerOfTheProduct([])]);
    }

    function it_does_nothing_when_product_does_not_exist(ExecutionContext $context)
    {
        $command = new UpsertProductCommand(userId: 1, identifierOrUuid: 'unknown');
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_validates_when_the_product_does_not_have_any_category(ExecutionContext $context)
    {
        $command = new UpsertProductCommand(userId: 1, identifierOrUuid: 'product_without_category');
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_validates_when_the_product_has_owned_category(
        GetOwnedCategories $getOwnedCategories,
        ExecutionContext $context
    ) {
        $command = new UpsertProductCommand(userId: 1, identifierOrUuid: 'product_with_category');
        $getOwnedCategories->forUserId(['master', 'print'], 1)->willReturn(['master']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new IsUserOwnerOfTheProduct());
    }

    function it_adds_a_violation_when_the_product_does_not_have_owned_category(
        GetOwnedCategories $getOwnedCategories,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new IsUserOwnerOfTheProduct();

        $command = new UpsertProductCommand(userId: 1, identifierOrUuid: 'product_with_category');
        $getOwnedCategories->forUserId(['master', 'print'], 1)->willReturn([]);
        $context->buildViolation($constraint->message)->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('userId')->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode('3')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($command, $constraint);
    }
}
