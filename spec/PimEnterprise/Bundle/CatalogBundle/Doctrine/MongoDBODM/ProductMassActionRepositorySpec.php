<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\PublishedProductRepository;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class ProductMassActionRepositorySpec extends ObjectBehavior
{
    function let(DocumentManager $dm, FamilyRepository $familyRepository, PublishedProductRepository $publishedRepository)
    {
        $this->beConstructedWith($dm, Argument::any(), $familyRepository, $publishedRepository);
    }

    function it_throws_an_exception_if_there_is_a_product_published($publishedRepository)
    {
        $ids = [1, 2];
        $publishedRepository->getProductIdsMapping($ids)->willReturn([1]);

        $this
            ->shouldThrow(
                new \Exception('Impossible to mass delete products. You should not have any published products in your selection.')
            )
            ->duringDeleteFromIds($ids);
    }
}
