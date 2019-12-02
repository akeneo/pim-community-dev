<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\Constraints\Product\ProductCategories;
use Pim\Component\Catalog\Validator\Constraints\Product\ProductCategoriesValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProductCategoriesValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context
    ) {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductCategoriesValidator::class);
    }

    function it_validates_nothing_if_entity_does_not_have_any_category(
        $context,
        ProductInterface $product,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new ProductCategories();

        $product->getCategories()->willReturn([]);

        $context->buildViolation(ProductCategories::ERROR_MESSAGE)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldNotBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_any_violation_if_no_categoryis_root(
        $context,
        CategoryInterface $category1,
        CategoryInterface $category2,
        ProductInterface $product,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new ProductCategories();

        $product->getCategories()->willReturn([$category1]);
        $category1->getParent()->willReturn($category2);

        $context->buildViolation(ProductCategories::ERROR_MESSAGE)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldNotBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_to_the_context_if_the_product_is_updated_with_a_root_category(
        $context,
        CategoryInterface $category1,
        CategoryInterface $category2,
        CategoryInterface $category3,
        ProductInterface $product,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new ProductCategories();

        $product->getCategories()->willReturn([$category1, $category2]);
        $category1->getParent()->willReturn($category3);
        $category2->getParent()->willReturn(null);

        $context->buildViolation(ProductCategories::ERROR_MESSAGE)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }
}
