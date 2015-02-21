<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductAssociation;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

class RelatedAssociationPublisherSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Publisher\Product\RelatedAssociationPublisher');
    }

    function it_is_a_publisher()
    {
        $this->shouldImplement('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function let(
        PublishedProductRepositoryInterface $publishedRepository,
        PublishedAssociationRepositoryInterface $publishedAssociationRepository,
        AssociationRepositoryInterface $associationRepository
    ) {
        $this->beConstructedWith($publishedRepository, $publishedAssociationRepository, $associationRepository);
    }

    function it_updates_associations_which_have_the_published_product(
        $publishedRepository,
        $associationRepository,
        $publishedAssociationRepository,
        PublishedProductInterface $published,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        Association $association1,
        AssociationTypeInterface $type1,
        Association $association3,
        AssociationTypeInterface $type3,
        PublishedProductAssociation $publishedAssociation
    ) {
        $product1->getId()->willReturn('original1');
        $product2->getId()->willReturn('original2');
        $product3->getId()->willReturn('original3');
        $published->getOriginalProduct()->willReturn($product2);

        $publishedRepository->getProductIdsMapping()->willReturn(
            [
                'original1' => 'published1',
                'original2' => 'published2',
                'original3' => 'published3',
            ]
        );

        $association1->getAssociationType()->willReturn($type1);
        $association1->getOwner()->willReturn($product1);
        $association3->getAssociationType()->willReturn($type3);
        $association3->getOwner()->willReturn($product3);

        $associationRepository->findByProductAndOwnerIds($product2, ['original1', 'original3'])
            ->willReturn([$association1, $association3]);

        $publishedAssociationRepository->findOneByTypeAndOwner($type1, 'published1')->willReturn($publishedAssociation);
        $publishedAssociationRepository->findOneByTypeAndOwner($type3, 'published3')->willReturn(null);

        $publishedAssociation->addProduct($published)->shouldBeCalled();

        $this->publish($published);
    }
}
