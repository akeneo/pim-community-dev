<?php

namespace spec\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\VariantProductRatio;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;

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
