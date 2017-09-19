<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\FindVariantProductCompleteness;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\ProductModel\Query\FindVariantProductCompletenessInterface;
use Prophecy\Argument;

class FindVariantProductCompletenessSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager, ProductModel::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindVariantProductCompleteness::class);
    }

    function it_is_a_query()
    {
        $this->shouldImplement(FindVariantProductCompletenessInterface::class);
    }
}
