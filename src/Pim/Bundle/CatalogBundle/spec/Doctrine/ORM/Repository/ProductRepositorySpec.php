<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Prophecy\Argument;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        ClassMetadata $class,
        ConfigurationRegistryInterface $registry,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GroupRepositoryInterface $groupRepository
    ) {
        $class->name = 'Pim\Component\Catalog\Model\Product';
        $this->beConstructedWith($em, $class);
        $this->setReferenceDataRegistry($registry);
        $this->setProductQueryBuilderFactory($pqbFactory);
        $this->setGroupRepository($groupRepository);
    }

    function it_has_group_repository(GroupRepositoryInterface $groupRepository)
    {
        $this->setGroupRepository($groupRepository)->shouldReturn($this);
    }

    function it_is_a_product_repository()
    {
        $this->shouldImplement('Pim\Component\Catalog\Repository\ProductRepositoryInterface');
    }

    function it_is_an_object_repository()
    {
        $this->shouldImplement('Doctrine\Common\Persistence\ObjectRepository');
    }

    function it_returns_eligible_products_for_variant_group(
        $groupRepository,
        $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        AttributeInterface $size,
        AttributeInterface $color,
        GroupInterface $variant,
        GroupTypeInterface $groupType,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $groupRepository->find(10)->willReturn($variant);
        $pqbFactory->create()->willReturn($pqb);

        $variant->getAxisAttributes()->willReturn([$size, $color]);
        $variant->getType()->willReturn($groupType);
        $groupType->isVariant()->willReturn(true);
        $size->getCode()->willReturn('size');
        $color->getCode()->willReturn('color');

        $pqb->addFilter('size', Operators::IS_NOT_EMPTY, Argument::any())->shouldBeCalled();
        $pqb->addFilter('color', Operators::IS_NOT_EMPTY, Argument::any())->shouldBeCalled();

        $pqb->execute()->willReturn([$product1, $product2, $product3]);
        $product1->getId()->willReturn(42);
        $product2->getId()->willReturn(56);
        $product3->getId()->willReturn(69);

        $this->getEligibleProductsForVariantGroup(10)->shouldReturn([$product1, $product2, $product3]);
    }

    function it_checks_if_the_product_has_an_attribute_in_its_variant_group(
        $em,
        GroupRepositoryInterface $groupRepository,
        QueryBuilder $queryBuilder,
        AbstractQuery $query
    ) {
        $this->setGroupRepository($groupRepository);

        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('p')->willReturn($queryBuilder);
        $queryBuilder->select('g.id')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::type('string'), "p", null)->willReturn($queryBuilder);
        $queryBuilder->leftJoin('p.groups', 'g')->willReturn($queryBuilder);
        $queryBuilder->where('p.id = :id')->willReturn($queryBuilder);
        $queryBuilder->setParameters([
            'id' => 10,
        ])->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getScalarResult()->willReturn([
            ['id' => 1],
            ['id' => 2]
        ]);

        $groupRepository->hasAttribute([1, 2], 'attribute_code')->willReturn(true);

        $this->hasAttributeInVariantGroup(10, 'attribute_code')->shouldReturn(true);
    }

    function it_checks_if_the_product_has_an_attribute_in_its_variant_group_but_it_has_not_group(
        $em,
        GroupRepositoryInterface $groupRepository,
        QueryBuilder $queryBuilder,
        AbstractQuery $query
    ) {
        $this->setGroupRepository($groupRepository);

        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('p')->willReturn($queryBuilder);
        $queryBuilder->select('g.id')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::type('string'), "p", null)->willReturn($queryBuilder);
        $queryBuilder->leftJoin('p.groups', 'g')->willReturn($queryBuilder);
        $queryBuilder->where('p.id = :id')->willReturn($queryBuilder);
        $queryBuilder->setParameters([
            'id' => 10,
        ])->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $query->getScalarResult()->willReturn([
            ['id' => null],
        ]);

        $groupRepository->hasAttribute(Argument::cetera())->shouldNotBeCalled();

        $this->hasAttributeInVariantGroup(10, 'attribute_code')->shouldReturn(false);
    }

    function it_checks_if_the_product_has_an_attribute_in_its_family(
        $em,
        QueryBuilder $queryBuilder,
        AbstractQuery $query
    ) {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('p')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::type('string'), "p", null)->willReturn($queryBuilder);
        $queryBuilder->leftJoin('p.family', 'f')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('f.attributes', 'a')->willReturn($queryBuilder);
        $queryBuilder->where('p.id = :id')->willReturn($queryBuilder);
        $queryBuilder->andWhere('a.code = :code')->willReturn($queryBuilder);
        $queryBuilder->setMaxResults(1)->willReturn($queryBuilder);
        $queryBuilder->setParameters([
            'id' => 10,
            'code' => 'attribute_code',
        ])->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $query->getArrayResult()->willReturn(['id' => 10]);
        $this->hasAttributeInFamily(10, 'attribute_code')->shouldReturn(true);

        $query->getArrayResult()->willReturn([]);
        $this->hasAttributeInFamily(10, 'attribute_code')->shouldReturn(false);
    }

    function it_count_all_products($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('p')->willReturn($queryBuilder);
        $queryBuilder->from('Pim\Component\Catalog\Model\Product', 'p', null)->willReturn($queryBuilder);
        $queryBuilder->select('COUNT(p.id)')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled();

        $this->countAll();
    }
}
