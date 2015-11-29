<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactory;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Prophecy\Argument;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class, ConfigurationRegistryInterface $registry, ProductQueryBuilderFactory $pqbFactory)
    {
        $class->name = 'Pim\Component\Catalog\Model\Product';
        $this->beConstructedWith($em, $class);
        $this->setReferenceDataRegistry($registry);
        $this->setProductQueryBuilderFactory($pqbFactory);
    }

    function it_is_a_product_repository()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface');
    }

    function it_is_an_object_repository()
    {
        $this->shouldImplement('Doctrine\Common\Persistence\ObjectRepository');
    }

    function it_returns_eligible_products_for_variant_group($em, $class, Statement $statement, Connection $connection)
    {
        $em->getClassMetadata(Argument::any())->willReturn($class);
        $em->getConnection()->willReturn($connection);

        $variantGroupId = 10;
        $connection->prepare(Argument::any())->willReturn($statement);
        $statement->bindValue('groupId', $variantGroupId)->shouldBeCalled();
        $statement->execute()->willReturn(null);
        $statement->fetchAll()->willReturn([
            ['product_id' => 1],
            ['product_id' => 2],
        ]);

        $this->getEligibleProductIdsForVariantGroup($variantGroupId)->shouldReturn([1, 2]);
    }

    function it_does_join_product_attribute_and_values_but_not_translations_when_find_one_product(
        $em,
        $pqbFactory,
        QueryBuilder $queryBuilder,
        Expr $expr,
        AbstractQuery $query,
        ProductQueryBuilder $pqb
    )
    {
        $pqbFactory->create()->willReturn($pqb);
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $pqb->getQueryBuilder()->willReturn($queryBuilder);

        $queryBuilder->getRootAliases()->willReturn(['p']);
        $queryBuilder->leftJoin('p.values', 'Value')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('Value.options', 'ValueOption')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('Value.attribute', 'Attribute')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('Value.options', 'ValueOption')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('ValueOption.optionValues', 'AttributeOptionValue')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('Attribute.availableLocales', 'AttributeLocales')->willReturn($queryBuilder);
        $queryBuilder->addSelect('Value')->willReturn($queryBuilder);
        $queryBuilder->addSelect('Attribute')->willReturn($queryBuilder);
        $queryBuilder->addSelect('AttributeLocales')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('Attribute.group', 'AttributeGroup')->willReturn($queryBuilder);
        $queryBuilder->addSelect('AttributeGroup')->willReturn($queryBuilder);
        $queryBuilder->expr()->willReturn($expr);
        $queryBuilder->andWhere(Argument::any())->willReturn($queryBuilder);

        $queryBuilder->leftJoin('Attribute.translations', 'AttributeTranslations')->shouldNotBeCalled();
        $queryBuilder->leftJoin('AttributeGroup.translations', 'AGroupTranslations')->shouldNotBeCalled();
        $queryBuilder->addSelect('AttributeTranslations')->shouldNotBeCalled();
        $queryBuilder->addSelect('AGroupTranslations')->shouldNotBeCalled();

        $queryBuilder->getQuery()->willReturn($query);
        $query->getOneOrNullResult()->shouldBeCalled();

        $this->findOneByWithValues([42]);
    }

    function it_count_all_products($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('p')->willReturn($queryBuilder);
        $queryBuilder->from('Pim\Component\Catalog\Model\Product', 'p')->willReturn($queryBuilder);
        $queryBuilder->select('COUNT(p.id)')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled();

        $this->countAll();
    }
}

