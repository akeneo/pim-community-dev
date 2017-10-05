<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\CompletenessGridFilter;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\ProductModel\Query\CompletenessGridFilterInterface;
use Prophecy\Argument;

class CompletenessGridFilterSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessGridFilter::class);
    }

    function it_it_is_a_query()
    {
        $this->shouldImplement(CompletenessGridFilterInterface::class);
    }
}
