<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

class CheckPublishedProductOnRemovalSubscriberSpec extends ObjectBehavior
{
    function let(PublishedProductRepositoryInterface $publishedRepository)
    {
        $this->beConstructedWith($publishedRepository);
    }

    function it_subscribes_to_pre_remove_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
                CatalogEvents::PRE_REMOVE_PRODUCT          => 'checkProductHasBeenPublished',
                CatalogEvents::PRE_REMOVE_FAMILY           => 'checkFamilyLinkedToPublishedProduct',
                CatalogEvents::PRE_REMOVE_ATTRIBUTE        => 'checkAttributeLinkedToPublishedProduct',
                CatalogEvents::PRE_REMOVE_CATEGORY         => 'checkCategoryLinkedToPublishedProduct',
                CatalogEvents::PRE_REMOVE_ASSOCIATION_TYPE => 'checkAssociationTypeLinkedToPublishedProduct',
                CatalogEvents::PRE_REMOVE_GROUP            => 'checkGroupLinkedToPublishedProduct'
        ]);
    }

    function it_checks_if_a_product_is_not_published(
        $publishedRepository,
        AbstractProduct $product,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $publishedRepository->findOneByOriginalProductId(1)->willReturn(false);

        $this->checkProductHasBeenPublished($event);
    }

    function it_throws_an_exception_if_the_product_is_published(
        $publishedRepository,
        AbstractProduct $product,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $publishedRepository->findOneByOriginalProductId(1)->willReturn(true);

        $this
            ->shouldThrow(new ConflictHttpException('Impossible to remove a published product'))
            ->during('checkProductHasBeenPublished', [$event]);
    }

    function it_checks_if_the_family_is_linked_to_a_published_product(
        $publishedRepository,
        Family $family,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($family);
        $publishedRepository->countPublishedProductsForFamily($family)->willReturn(0);

        $this->checkFamilyLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_family_is_linked_to_a_published_product(
        $publishedRepository,
        Family $family,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($family);
        $publishedRepository->countPublishedProductsForFamily($family)->willReturn(1);

        $this
            ->shouldThrow(new ConflictHttpException('Impossible to remove family linked to a published product'))
            ->during('checkFamilyLinkedToPublishedProduct', [$event]);
    }

    function it_checks_if_the_attribute_is_linked_to_a_published_product(
        $publishedRepository,
        AbstractAttribute $attribute,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($attribute);
        $publishedRepository->countPublishedProductsForAttribute($attribute)->willReturn(0);

        $this->checkAttributeLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_attribute_is_linked_to_a_published_product(
        $publishedRepository,
        AbstractAttribute $attribute,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($attribute);
        $publishedRepository->countPublishedProductsForAttribute($attribute)->willReturn(1);

        $this
            ->shouldThrow(new ConflictHttpException('Impossible to remove attribute linked to a published product'))
            ->during('checkAttributeLinkedToPublishedProduct', [$event]);
    }

    function it_checks_if_the_category_is_linked_to_a_published_product(
        $publishedRepository,
        CategoryInterface $category,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($category);
        $publishedRepository->countPublishedProductsForCategory($category)->willReturn(0);

        $this->checkCategoryLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_category_is_linked_to_a_published_product(
        $publishedRepository,
        CategoryInterface $category,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($category);
        $publishedRepository->countPublishedProductsForCategory($category)->willReturn(1);

        $this
            ->shouldThrow(new ConflictHttpException('Impossible to remove category linked to a published product'))
            ->during('checkCategoryLinkedToPublishedProduct', [$event]);
    }

    function it_checks_if_the_group_is_linked_to_a_published_product(
        $publishedRepository,
        Group $group,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($group);
        $publishedRepository->countPublishedProductsForGroup($group)->willReturn(0);

        $this->checkGroupLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_group_is_linked_to_a_published_product(
        $publishedRepository,
        Group $group,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($group);
        $publishedRepository->countPublishedProductsForGroup($group)->willReturn(1);

        $this
            ->shouldThrow(new ConflictHttpException('Impossible to remove group linked to a published product'))
            ->during('checkGroupLinkedToPublishedProduct', [$event]);
    }

    function it_checks_if_the_association_type_is_linked_to_a_published_product(
        $publishedRepository,
        AssociationType $associationType,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($associationType);
        $publishedRepository->countPublishedProductsForAssociationType($associationType)->willReturn(0);

        $this->checkAssociationTypeLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_association_type_is_linked_to_a_published_product(
        $publishedRepository,
        AssociationType $associationType,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($associationType);
        $publishedRepository->countPublishedProductsForAssociationType($associationType)->willReturn(1);

        $this
            ->shouldThrow(
                new ConflictHttpException('Impossible to remove association type linked to a published product')
            )
            ->during('checkAssociationTypeLinkedToPublishedProduct', [$event]);
    }
}
