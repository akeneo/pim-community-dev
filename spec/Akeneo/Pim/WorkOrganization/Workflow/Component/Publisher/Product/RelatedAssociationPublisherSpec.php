<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductAssociation;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedAssociationRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;

class RelatedAssociationPublisherSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product\RelatedAssociationPublisher');
    }

    function it_is_a_publisher()
    {
        $this->shouldImplement('Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface');
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
        AssociationInterface $association1,
        AssociationTypeInterface $type1,
        AssociationInterface $association3,
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
