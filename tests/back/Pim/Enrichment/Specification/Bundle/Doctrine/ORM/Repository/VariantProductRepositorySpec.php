<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\VariantProductRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\VariantProductRepositoryInterface;
use Prophecy\Argument;

class VariantProductRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        ClassMetadata $classMetadata
    ) {
        $classMetadata->name = Product::class;
        $em->getClassMetadata(Product::class)->willReturn($classMetadata);
        $this->beConstructedWith($em, Product::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VariantProductRepository::class);
    }

    function it_is_a_variant_product_repository()
    {
        $this->shouldImplement(VariantProductRepositoryInterface::class);
    }

    function it_finds_siblings_variant_products_for_an_existing_variant_product(
        $em,
        ProductInterface $variantProduct,
        ProductModelInterface $productModel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sibling
    ) {
        $variantProduct->getParent()->willReturn($productModel);
        $variantProduct->getId()->willReturn(42);

        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('vp')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::type('string'), "vp", null)->willReturn($queryBuilder);
        $queryBuilder->where('vp.parent = :parent')->willReturn($queryBuilder);
        $queryBuilder->setParameter('parent', $productModel)->willReturn($queryBuilder);
        $queryBuilder->andWhere('vp.id != :id')->willReturn($queryBuilder);
        $queryBuilder->setParameter('id', 42)->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn([$sibling]);

        $this->findSiblingsProducts($variantProduct)->shouldReturn([$sibling]);
    }

    function it_finds_siblings_variant_products_for_a_new_variant_product(
        $em,
        ProductInterface $variantProduct,
        ProductModelInterface $productModel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sibling
    ) {
        $variantProduct->getParent()->willReturn($productModel);
        $variantProduct->getId()->willReturn(null);

        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('vp')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::type('string'), "vp", null)->willReturn($queryBuilder);
        $queryBuilder->where('vp.parent = :parent')->willReturn($queryBuilder);
        $queryBuilder->setParameter('parent', $productModel)->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn([$sibling]);

        $this->findSiblingsProducts($variantProduct)->shouldReturn([$sibling]);
    }
}
