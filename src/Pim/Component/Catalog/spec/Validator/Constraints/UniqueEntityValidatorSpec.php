<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Validator\Constraints\UniqueEntity;
use Pim\Component\Catalog\Validator\Constraints\UniqueEntityValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueEntityValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context,
        ObjectManager $objectManager
    ) {
        $this->beConstructedWith($objectManager);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueEntityValidator::class);
    }

    function it_adds_violation_to_the_context_if_a_product_already_exist_in_the_database(
        $context,
        $objectManager,
        ProductRepositoryInterface $productRepository,
        ProductInterface $product,
        ProductInterface $productInDatabase,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueEntity();
        $constraint->entityClass = ProductInterface::class;

        $objectManager->getRepository(ProductInterface::class)->willReturn($productRepository);

        $product->getIdentifier()->willReturn('identifier');
        $productRepository->findOneBy(['identifier' => 'identifier'])->willReturn($productInDatabase);

        $productInDatabase->getId()->willReturn(40);
        $product->getId()->willReturn(64);

        $context->buildViolation('The same identifier is already set on another product')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('identifier')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->ShouldBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_do_nothing_if_the_product_does_not_exist_in_database(
        $objectManager,
        $context,
        ProductRepositoryInterface $productRepository,
        ProductInterface $product
    ) {
        $constraint = new UniqueEntity();
        $constraint->entityClass = ProductInterface::class;

        $objectManager->getRepository(ProductInterface::class)->willReturn($productRepository);

        $product->getIdentifier()->willReturn('identifier');
        $productRepository->findOneBy(['identifier' => 'identifier'])->willReturn(null);

        $context->buildViolation('The same identifier is already set on another product')->shouldNotBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_excepted_entity_is_not_a_product(
        CategoryInterface $category
    ) {
        $constraint = new UniqueEntity();
        $constraint->entityClass = ProductInterface::class;

        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$category, $constraint]);
    }

    function it_throws_an_exception_if_the_excepted_constraint_is_not_a_unique_product_constraint(
        ProductInterface $product,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }

    function it_throws_an_exception_if_the_entity_name_is_not_given(
        ProductInterface $product
    ) {
        $constraint = new UniqueEntity();

        $this->shouldThrow(InvalidArgumentException::class)->during('validate', [$product, $constraint]);
    }
}
