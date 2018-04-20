<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints\Product;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductEntity;
use Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductEntityValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueProductEntityValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context,
        IdentifiableObjectRepositoryInterface $objectRepository
    ) {
        $this->beConstructedWith($objectRepository);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueProductEntityValidator::class);
    }

    function it_adds_violation_to_the_context_if_a_product_already_exist_in_the_database(
        $context,
        $objectRepository,
        ProductInterface $product,
        ProductInterface $productInDatabase,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueProductEntity();

        $product->getIdentifier()->willReturn('identifier');
        $objectRepository->findOneByIdentifier('identifier')->willReturn($productInDatabase);

        $productInDatabase->getId()->willReturn(40);
        $product->getId()->willReturn(64);

        $context->buildViolation('The same identifier is already set on another product')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('identifier')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->ShouldBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_do_nothing_if_the_product_does_not_exist_in_database(
        $context,
        $objectRepository,
        ProductInterface $product
    ) {
        $constraint = new UniqueProductEntity();

        $product->getIdentifier()->willReturn('identifier');
        $objectRepository->findOneByIdentifier('identifier')->willReturn(null);

        $context->buildViolation('The same identifier is already set on another product')->shouldNotBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_excepted_entity_is_not_a_product(
        CategoryInterface $category
    ) {
        $constraint = new UniqueProductEntity();

        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$category, $constraint]);
    }

    function it_throws_an_exception_if_the_excepted_constraint_is_not_a_unique_product_constraint(
        ProductInterface $product,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }
}
