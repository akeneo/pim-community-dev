<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Prophecy\Argument;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        ClassMetadata $class,
        ConfigurationRegistryInterface $registry
    ) {
        $class->name = Product::class;
        $this->beConstructedWith($em, $class);
    }

    function it_is_a_product_repository()
    {
        $this->shouldImplement(ProductRepositoryInterface::class);
    }

    function it_is_an_object_repository()
    {
        $this->shouldImplement('Doctrine\Common\Persistence\ObjectRepository');
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
        $queryBuilder->from(Product::class, 'p', null)->willReturn($queryBuilder);
        $queryBuilder->select('COUNT(p.id)')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled();

        $this->countAll();
    }
}
