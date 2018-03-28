<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\CompleteFilter;
use Pim\Component\Catalog\ProductAndProductModel\Query\CompleteFilterInterface;

class CompleteFilterSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompleteFilter::class);
    }

    function it_it_is_a_query()
    {
        $this->shouldImplement(CompleteFilterInterface::class);
    }
}
