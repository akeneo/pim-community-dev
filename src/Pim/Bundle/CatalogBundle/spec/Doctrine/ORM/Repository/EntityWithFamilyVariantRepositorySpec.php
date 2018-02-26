<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\EntityWithFamilyVariantRepository;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\EntityWithFamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\VariantProductRepositoryInterface;
use Prophecy\Argument;

class EntityWithFamilyVariantRepositorySpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        VariantProductRepositoryInterface $variantProductRepository
    ) {
        $this->beConstructedWith($productModelRepository, $variantProductRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EntityWithFamilyVariantRepository::class);
    }

    function it_is_an_entity_with_family_variant_repository()
    {
        $this->shouldImplement(EntityWithFamilyVariantRepositoryInterface::class);
    }

    function it_finds_no_siblings_if_entity_has_no_family_variant(
        $variantProductRepository,
        $productModelRepository,
        ProductInterface $variantProduct,
        ProductModelInterface $productModel
    ) {
        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getFamilyVariant()->willReturn(null);
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->isRootProductModel()->shouldNotBeCalled();

        $variantProductRepository->findSiblingsProducts(Argument::any())->shouldNotBeCalled();
        $productModelRepository->findSiblingsProductModels(Argument::any())->shouldNotBeCalled();

        $this->findSiblings($variantProduct)->shouldReturn([]);
        $this->findSiblings($productModel)->shouldReturn([]);
    }

    function it_find_no_siblings_if_entity_is_a_root_product_model(
        $variantProductRepository,
        $productModelRepository,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->isRootProductModel()->willReturn(true);

        $productModelRepository->findSiblingsProductModels($productModel)->willReturn();

        $variantProductRepository->findSiblingsProducts(Argument::any())->shouldNotBeCalled();

        $this->findSiblings($productModel)->shouldReturn([]);
    }

    function it_finds_the_siblings_of_a_product_model(
        $variantProductRepository,
        $productModelRepository,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $sibling1,
        ProductModelInterface $sibling2
    ) {
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->isRootProductModel()->willReturn(false);

        $productModelRepository->findSiblingsProductModels($productModel)->willReturn([$sibling1, $sibling2]);

        $variantProductRepository->findSiblingsProducts(Argument::any())->shouldNotBeCalled();

        $this->findSiblings($productModel)->shouldReturn([$sibling1, $sibling2]);
    }

    function it_finds_the_siblings_of_a_variant_product(
        $variantProductRepository,
        $productModelRepository,
        ProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        ProductInterface $sibling1,
        ProductInterface $sibling2
    ) {
        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);

        $variantProductRepository->findSiblingsProducts($variantProduct)->willReturn([$sibling1, $sibling2]);

        $productModelRepository->findSiblingsProductModels(Argument::any())->shouldNotBeCalled();

        $this->findSiblings($variantProduct)->shouldReturn([$sibling1, $sibling2]);
    }
}
