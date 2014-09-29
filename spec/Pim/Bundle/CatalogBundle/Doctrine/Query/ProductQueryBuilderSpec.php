<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Query;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

class ProductQueryBuilderSpec extends ObjectBehavior
{
    function let(CatalogContext $context, AttributeRepository $repository)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context, $repository);
    }

    function it_is_a_product_query_builder()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface');
    }
}
