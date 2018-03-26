<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\ConvertProductToVariantProduct;

class ConvertProductToVariantProductSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConvertProductToVariantProduct::class);
    }
}
