<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Query;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFilterRegistryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQuerySorterRegistryInterface;

class ProductQueryBuilderSpec extends ObjectBehavior
{
    function let(CatalogContext $context, AttributeRepository $repository, ProductQueryFilterRegistryInterface $filterRegistry, ProductQuerySorterRegistryInterface $sorterRegistry)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context, $repository, $filterRegistry, $sorterRegistry);
    }

    function it_is_a_product_query_builder()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface');
    }
}
