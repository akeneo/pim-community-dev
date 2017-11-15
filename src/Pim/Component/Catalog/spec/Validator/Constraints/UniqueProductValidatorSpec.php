<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\ConstraintsProductValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueProductValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context,
        IdentifiableObjectRepositoryInterface $productRepository
    ) {
        $this->initialize($context);

        $this->beConstructedWith($productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConstraintsProductValidator::class);
    }

    function it adds violation to the context if a product already exist in the database(
        $context,
        $productRepository,
        ProductInterface $product,
        ProductInterface $productInDatabase,
        Constraint $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $productRepository->findOneByIdentifier()->willReturn($productInDatabase);
        $productInDatabase->getId()->willReturn(40);
        $product->getId()->willReturn(40);

        $context->buildViolation('pim_catalog.constraint.pim_immutable_product_validator')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('identifier')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->ShouldBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it do nothing if the product does not exist in database(
        $productRepository,
        $context,
        ProductInterface $product,
        Constraint $constraint
    ) {
        $productRepository->findOneByIdentifier()->willReturn(null);

        $context->buildViolation('pim_catalog.constraint.pim_immutable_product_validator')->shouldNotBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it throws an exception if the excepted entity is not a product(
        CategoryInterface $catagory,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$catagory, $constraint]);
    }

    function it throws an exception if the excepted constraint is not a unique product contraint(
        ProductInterface $product,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }
}
