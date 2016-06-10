<?php

namespace spec\Pim\Bundle\ImportExportBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class ProductSearchableRepositorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($productQueryBuilderFactory, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Doctrine\MongoDBODM\Repository\ProductSearchableRepository');
    }

    function it_is_a_searchable_repository()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Doctrine\Commun\AbstractProductSearchableRepository');
    }

    function it_searches_the_list_of_the_identifiers(
        $productQueryBuilderFactory,
        $attributeRepository,
        AttributeInterface $attribute,
        ProductQueryBuilderInterface $productQueryBuilder,
        Builder $queryBuilder,
        Query $query,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValueInterface $identifier1,
        ProductValueInterface $identifier2
    ) {
        $attribute->getCode()->willReturn('identifier');
        $attributeRepository->findOneBy(['attributeType' => AttributeTypes::IDENTIFIER])->willReturn($attribute);

        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);
        $productQueryBuilder->addFilter('identifier', Operators::CONTAINS, 'sku')->shouldBeCalled();

        $productQueryBuilder->getQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->limit(10)->willReturn($queryBuilder);
        $queryBuilder->skip(50)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);

        $query->execute()->willReturn([
            $product1,
            $product2
        ]);
        
        $identifier1->getData()->willReturn('sku1');
        $product1->getIdentifier()->willReturn($identifier1);

        $identifier2->getData()->willReturn('sku2');
        $product2->getIdentifier()->willReturn($identifier2);
        
        $this->findBySearch('sku', [
            'limit' => 10,
            'page' => 5
        ])->shouldReturn([
            ['id' => 'sku1', 'text' => 'sku1'],
            ['id' => 'sku2', 'text' => 'sku2'],
        ]);
    }

    function it_searches_the_list_of_the_identifiers_without_page_option(
        $productQueryBuilderFactory,
        $attributeRepository,
        AttributeInterface $attribute,
        ProductQueryBuilderInterface $productQueryBuilder,
        Builder $queryBuilder,
        Query $query,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValueInterface $identifier1,
        ProductValueInterface $identifier2
    ) {
        $attribute->getCode()->willReturn('identifier');
        $attributeRepository->findOneBy(['attributeType' => AttributeTypes::IDENTIFIER])->willReturn($attribute);

        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);
        $productQueryBuilder->addFilter('identifier', Operators::CONTAINS, 'sku')->shouldBeCalled();

        $productQueryBuilder->getQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->limit(10)->willReturn($queryBuilder);
        $queryBuilder->skip(Argument::any())->shouldNotBeCalled();
        $queryBuilder->getQuery()->willReturn($query);

        $query->execute()->willReturn([
            $product1,
            $product2
        ]);

        $identifier1->getData()->willReturn('sku1');
        $product1->getIdentifier()->willReturn($identifier1);

        $identifier2->getData()->willReturn('sku2');
        $product2->getIdentifier()->willReturn($identifier2);

        $this->findBySearch('sku', [
            'limit' => 10,
            'page' => 1
        ])->shouldReturn([
            ['id' => 'sku1', 'text' => 'sku1'],
            ['id' => 'sku2', 'text' => 'sku2'],
        ]);
    }
}
