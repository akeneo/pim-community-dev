<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\EntityManager;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\PublishedProductRepository;

class ProductMassActionRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, PublishedProductRepository $publishedRepository)
    {
        $this->beConstructedWith($em, Argument::any(), $publishedRepository);
    }

    function it_throws_an_exception_if_there_is_a_product_published($publishedRepository)
    {
        $ids = [1, 2];
        $publishedRepository->getProductIdsMapping($ids)->willReturn([1]);

        $this
            ->shouldThrow(
                new \Exception(
                    'Impossible to mass delete products. You should not have any published products in your selection.'
                )
            )
            ->duringDeleteFromIds($ids);
    }
}
