<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Validation\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Validation\ProductModel\IsNotRelatedToAPublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Validation\ProductModel\IsNotRelatedToAPublishedProductValidator;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class IsNotRelatedToAPublishedProductValidatorSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        PublishedProductRepositoryInterface $publishedRepository,
        ExecutionContextInterface $executionContext
    ) {
        $this->beConstructedWith($productModelRepository, $publishedRepository);
        $this->initialize($executionContext);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(IsNotRelatedToAPublishedProductValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new RemoveProductModelCommand('pm'), new IsNull()]);
    }

    function it_throws_an_exception_for_a_wrong_subject()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', ['test', new IsNotRelatedToAPublishedProduct()]);
    }

    function it_adds_a_violation_when_product_model_is_related_to_a_published_product(
        ProductModelRepositoryInterface $productModelRepository,
        PublishedProductRepositoryInterface $publishedRepository,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $command = new RemoveProductModelCommand('pm');
        $constraint = new IsNotRelatedToAPublishedProduct();

        $productModel = new ProductModel();
        $productModelRepository->findOneByIdentifier($command->productModelCode())->willReturn($productModel);
        $publishedRepository->countPublishedVariantProductsForProductModel($productModel)->willReturn(1);

        $executionContext->buildViolation($constraint->message)->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($command, $constraint);
    }

    function it_does_not_add_any_violation_when_product_model_is_not_related_to_a_published_product(
        ProductModelRepositoryInterface $productModelRepository,
        PublishedProductRepositoryInterface $publishedRepository,
        ExecutionContextInterface $executionContext
    ) {
        $command = new RemoveProductModelCommand('pm');
        $constraint = new IsNotRelatedToAPublishedProduct();

        $productModel = new ProductModel();
        $productModelRepository->findOneByIdentifier($command->productModelCode())->willReturn($productModel);
        $publishedRepository->countPublishedVariantProductsForProductModel($productModel)->willReturn(0);

        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, $constraint);
    }

    function it_does_not_add_any_violation_when_product_model_is_unknown(
        ProductModelRepositoryInterface $productModelRepository,
        ExecutionContextInterface $executionContext
    ) {
        $command = new RemoveProductModelCommand('pm');
        $constraint = new IsNotRelatedToAPublishedProduct();

        $productModelRepository->findOneByIdentifier($command->productModelCode())->willReturn(null);
        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, $constraint);
    }
}
