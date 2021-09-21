<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindProductAssociationToPublishByProductQueryInterface  as FindProductAssociationToPublish;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product\RelatedAssociationPublisher;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductAssociation;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedAssociationRepositoryInterface;
use Prophecy\Argument;

class RelatedAssociationPublisherSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RelatedAssociationPublisher::class);
    }

    function it_is_a_publisher()
    {
        $this->shouldImplement(PublisherInterface::class);
    }

    function let(
        PublishedAssociationRepositoryInterface       $publishedAssociationRepository,
        FindProductAssociationToPublish $findProductAssociationToPublishByProductQuery
    )
    {
        $this->beConstructedWith($publishedAssociationRepository, $findProductAssociationToPublishByProductQuery);
    }

    function it_updates_associations_which_have_the_published_product(
        PublishedAssociationRepositoryInterface       $publishedAssociationRepository,
        FindProductAssociationToPublish $findProductAssociationToPublishByProductQuery,
        PublishedProductInterface                     $published,
        ProductInterface                              $product1,
        ProductInterface                              $product2,
        ProductInterface                              $product3,
        PublishedProductAssociation                   $publishedAssociation
    )
    {
        $product1->getId()->willReturn('original1');
        $product2->getId()->willReturn('original2');
        $product3->getId()->willReturn('original3');
        $published->getOriginalProduct()->willReturn($product2);

        $findProductAssociationToPublishByProductQuery->execute($product2)->willReturn(
            [
                [FindProductAssociationToPublish::PRODUCT_ID => 'published1', FindProductAssociationToPublish::ASSOCIATION_TYPE_ID => 1],
                [FindProductAssociationToPublish::PRODUCT_ID => 'published3', FindProductAssociationToPublish::ASSOCIATION_TYPE_ID => 3]
            ]);


        $publishedAssociationRepository->findOneByTypeAndOwner(Argument::any(), 'published1')->willReturn($publishedAssociation);
        $publishedAssociationRepository->findOneByTypeAndOwner(Argument::any(), 'published3')->willReturn(null);

        $publishedAssociation->addProduct($published)->shouldBeCalled();

        $this->publish($published);
    }
}
