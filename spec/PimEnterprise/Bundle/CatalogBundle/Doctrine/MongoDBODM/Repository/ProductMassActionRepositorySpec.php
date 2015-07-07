<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\Repository\PublishedProductRepository;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class ProductMassActionRepositorySpec extends ObjectBehavior
{
    function let(
        DocumentManager $dm,
        FamilyRepositoryInterface $familyRepository,
        PublishedProductRepository $publishedRepository
    ) {
        $this->beConstructedWith($dm, Argument::any(), $familyRepository, $publishedRepository);
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
