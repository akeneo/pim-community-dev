<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Query;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\QueryFilterRegistryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\QuerySorterRegistryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeSorterInterface;

class ProductQueryBuilderSpec extends ObjectBehavior
{
    function let(CustomAttributeRepository $repository, QueryFilterRegistryInterface $filterRegistry, QuerySorterRegistryInterface $sorterRegistry, QueryBuilder $qb)
    {
        $this->beConstructedWith($repository, $filterRegistry, $sorterRegistry, ['locale' => 'en_US', 'scope' => 'print']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_product_query_builder()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface');
    }

    function it_adds_a_field_filter($repository, $filterRegistry, FieldFilterInterface $filter)
    {
        $repository->findOneByCode('id')->willReturn(null);
        $filterRegistry->getFieldFilter('id')->willReturn($filter);
        $filter->supportsOperator('=')->willReturn(true);
        $filter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $filter->addFieldFilter('id', '=', '42', 'en_US', 'print')->shouldBeCalled();

        $this->addFilter('id', '=', '42', []);
    }

    function it_adds_an_attribute_filter($repository, $filterRegistry, AttributeFilterInterface $filter, AttributeInterface $attribute)
    {
        $repository->findOneByCode('sku')->willReturn($attribute);
        $filterRegistry->getAttributeFilter($attribute)->willReturn($filter);
        $filter->supportsOperator('=')->willReturn(true);
        $filter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $filter->addAttributeFilter($attribute, '=', '42', 'en_US', 'print')->shouldBeCalled();

        $this->addFilter('sku', '=', '42', []);
    }

    function it_adds_a_field_sorter($repository, $sorterRegistry, FieldSorterInterface $sorter)
    {
        $repository->findOneByCode('id')->willReturn(null);
        $sorterRegistry->getFieldSorter('id')->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addFieldSorter( 'id', 'DESC', 'en_US', 'print')->shouldBeCalled();

        $this->addSorter('id', 'DESC', []);
    }

    function it_adds_an_attribute_sorter($repository, $sorterRegistry, AttributeSorterInterface $sorter, AttributeInterface $attribute)
    {
        $repository->findOneByCode('sku')->willReturn($attribute);
        $sorterRegistry->getAttributeSorter($attribute)->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addAttributeSorter($attribute, 'DESC', 'en_US', 'print')->shouldBeCalled();

        $this->addSorter('sku', 'DESC', []);
    }

    function it_provides_a_query_builder_once_configured($qb)
    {
        $this->getQueryBuilder()->shouldReturn($qb);
    }

    function it_configures_the_query_builder($qb)
    {
        $this->setQueryBuilder($qb)->shouldReturn($this);
    }

    function it_executes_the_query($qb, AbstractQuery $query)
    {
        $qb->getQuery()->willReturn($query);
        $query->execute()->shouldBeCalled();

        $this->execute();
    }
}

class CustomAttributeRepository extends AttributeRepository
{
    public function findOneByCode()
    {
        return null;
    }
}
