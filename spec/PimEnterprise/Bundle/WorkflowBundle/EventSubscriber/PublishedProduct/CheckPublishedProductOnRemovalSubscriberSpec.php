<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Event;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CheckPublishedProductOnRemovalSubscriberSpec extends ObjectBehavior
{
    function let(PublishedProductRepositoryInterface $publishedRepository)
    {
        $this->beConstructedWith($publishedRepository);
    }

    function it_subscribes_to_pre_remove_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'preRemove',
        ]);
    }

    function it_checks_if_a_product_is_not_published($publishedRepository, GenericEvent $event)
    {
        $product = new Product();
        $event->getSubject()->willReturn($product);
        $publishedRepository->findOneByOriginalProduct($product)->willReturn(null);

        $this->preRemove($event);
    }

    function it_throws_an_exception_if_the_product_is_published(
        $publishedRepository,
        PublishedProductInterface $published,
        GenericEvent $event
    ) {
        $product = new Product();
        $event->getSubject()->willReturn($product);
        $publishedRepository->findOneByOriginalProduct($product)->willReturn($published);

        $this
            ->shouldThrow(new PublishedProductConsistencyException('Impossible to remove a published product'))
            ->duringPreRemove($event);
    }

    function it_checks_if_the_family_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $family = new Family();
        $event->getSubject()->willReturn($family);
        $publishedRepository->countPublishedProductsForFamily($family)->willReturn(0);

        $this->preRemove($event);
    }

    function it_throws_an_exception_if_the_family_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $family = new Family();
        $event->getSubject()->willReturn($family);
        $publishedRepository->countPublishedProductsForFamily($family)->willReturn(1);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove family linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_checks_if_the_attribute_is_linked_to_a_published_product($publishedRepository, GenericEvent $event)
    {
        $attribute = new Attribute();
        $event->getSubject()->willReturn($attribute);
        $publishedRepository->countPublishedProductsForAttribute($attribute)->willReturn(0);

        $this->preRemove($event);
    }

    function it_throws_an_exception_if_the_attribute_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $attribute = new Attribute();
        $event->getSubject()->willReturn($attribute);
        $publishedRepository->countPublishedProductsForAttribute($attribute)->willReturn(1);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove attribute linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_checks_if_the_category_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $category = new Category();
        $event->getSubject()->willReturn($category);
        $publishedRepository->countPublishedProductsForCategory($category)->willReturn(0);

        $this->preRemove($event);
    }

    function it_throws_an_exception_if_the_category_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $category = new Category();
        $event->getSubject()->willReturn($category);
        $publishedRepository->countPublishedProductsForCategory($category)->willReturn(2);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove category linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_checks_if_the_group_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $group = new Group();
        $event->getSubject()->willReturn($group);
        $publishedRepository->countPublishedProductsForGroup($group)->willReturn(0);

        $this->preRemove($event);
    }

    function it_throws_an_exception_if_the_group_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $group = new Group();
        $event->getSubject()->willReturn($group);
        $publishedRepository->countPublishedProductsForGroup($group)->willReturn(1);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove group linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_checks_if_the_association_type_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $associationType = new AssociationType();
        $event->getSubject()->willReturn($associationType);
        $publishedRepository->countPublishedProductsForAssociationType($associationType)->willReturn(0);

        $this->preRemove($event);
    }

    function it_throws_an_exception_if_the_association_type_is_linked_to_a_published_product(
        $publishedRepository,
        GenericEvent $event
    ) {
        $associationType = new AssociationType();
        $event->getSubject()->willReturn($associationType);
        $publishedRepository->countPublishedProductsForAssociationType($associationType)->willReturn(1);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException(
                    'Impossible to remove association type linked to a published product'
                )
            )
            ->duringPreRemove($event);
    }
}
