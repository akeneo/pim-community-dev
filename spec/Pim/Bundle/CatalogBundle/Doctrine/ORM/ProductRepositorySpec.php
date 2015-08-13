<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactory;
use Prophecy\Argument;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class, ProductQueryBuilderFactory $pqbFactory)
    {
        $class->name = 'Pim\Bundle\CatalogBundle\Model\Product';
        $this->beConstructedWith($em, $class);
        $this->setProductQueryBuilderFactory($pqbFactory);
    }

    function it_is_a_product_repository()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface');
    }

    function it_does_join_product_attribute_and_values_but_not_translations_when_find_one_product(
        $em,
        $pqbFactory,
        QueryBuilder $queryBuilder,
        Expr $expr,
        AbstractQuery $query,
        ProductQueryBuilder $pqb
    ) {
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
}
