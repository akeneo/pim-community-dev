<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\VariantProductRepository;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\VariantProductRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Prophecy\Argument;

class VariantProductRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        ClassMetadata $class
    ) {
        $class->name = 'Pim\Component\Catalog\Model\Product';
        $this->beConstructedWith($em, $class);
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
        VariantProductInterface $variantProduct,
        ProductModelInterface $productModel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        VariantProductInterface $sibling
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
        VariantProductInterface $variantProduct,
        ProductModelInterface $productModel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        VariantProductInterface $sibling
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
