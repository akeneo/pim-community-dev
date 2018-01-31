<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\VariantProductRatio;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\ProductModel\Query\VariantProductRatioInterface;
use Prophecy\Argument;

class VariantProductRatioSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VariantProductRatio::class);
    }

    function it_is_a_query()
    {
        $this->shouldImplement(VariantProductRatioInterface::class);
    }
}
