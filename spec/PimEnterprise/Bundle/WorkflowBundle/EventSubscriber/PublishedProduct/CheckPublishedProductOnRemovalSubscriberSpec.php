<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
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
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\Workflow\Exception\PublishedProductConsistencyException;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class CheckPublishedProductOnRemovalSubscriberSpec extends ObjectBehavior
{
    function let(
        PublishedProductRepositoryInterface $publishedRepository,
        ProductQueryBuilderFactoryInterface $queryBuilderFactory,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($publishedRepository, $queryBuilderFactory, $channelRepository, $localeRepository);
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
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(0);

        $family = new Family();
        $event->getSubject()->willReturn($family);

        $this
            ->shouldNotThrow(new PublishedProductConsistencyException('Impossible to remove a published product'))
            ->duringPreRemove($event);
    }

    function it_throws_an_exception_if_the_family_is_linked_to_a_published_product(
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('family', Operators::IN_LIST, ['family_code'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(1);

        $family = new Family();
        $family->setCode('family_code');
        $event->getSubject()->willReturn($family);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove family linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_checks_if_the_attribute_is_linked_to_a_published_product(
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code', Operators::IS_NOT_EMPTY, '')->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(0);

        $attribute = new Attribute();
        $event->getSubject()->willReturn($attribute);

        $this
            ->shouldNotThrow(new PublishedProductConsistencyException('Impossible to remove a published product'))
            ->duringPreRemove($event);
    }

    function it_throws_an_exception_if_the_attribute_is_linked_to_a_published_product(
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code', Operators::IS_NOT_EMPTY, '')->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(1);

        $attribute = new Attribute();
        $attribute->setCode('attribute_code');
        $event->getSubject()->willReturn($attribute);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove attribute linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_checks_if_the_localizable_attribute_is_linked_to_a_published_product(
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code', Operators::IS_NOT_EMPTY, '')->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(0);

        $attribute = new Attribute();
        $attribute->setLocalizable(true);
        $event->getSubject()->willReturn($attribute);

        $this
            ->shouldNotThrow(new PublishedProductConsistencyException('Impossible to remove a published product'))
            ->duringPreRemove($event);
    }

    function it_throws_an_exception_if_the_localizable_attribute_is_linked_to_a_published_product(
        $queryBuilderFactory,
        $localeRepository,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code', Operators::IS_NOT_EMPTY, '', ['locale' => 'fr_FR'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(0);

        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code', Operators::IS_NOT_EMPTY, '', ['locale' => 'en_US'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(2);

        $attribute = new Attribute();
        $attribute->setCode('attribute_code');
        $attribute->setLocalizable(true);

        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $event->getSubject()->willReturn($attribute);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove attribute linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_throws_an_exception_if_the_localizable_and_scopable_attribute_is_linked_to_a_published_product(
        $queryBuilderFactory,
        $localeRepository,
        $channelRepository,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor,
        ChannelInterface $ecommerceChannel
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code', Operators::IS_NOT_EMPTY, '', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(0);

        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code', Operators::IS_NOT_EMPTY, '', ['locale' => 'en_US', 'scope' => 'ecommerce'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(2);

        $attribute = new Attribute();
        $attribute->setCode('attribute_code');
        $attribute->setLocalizable(true);
        $attribute->setScopable(true);

        $channelRepository->findAll()->willReturn([$ecommerceChannel]);
        $ecommerceChannel->getLocaleCodes()->willReturn(['en_FR', 'fr_FR']);
        $ecommerceChannel->getCode()->willReturn('ecommerce');
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US', 'de_DE']);
        $event->getSubject()->willReturn($attribute);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove attribute linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_checks_if_the_category_is_linked_to_a_published_product(
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('categories', Operators::IN_CHILDREN_LIST, ['category_code'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(0);

        $category = new Category();
        $category->setCode('category_code');
        $event->getSubject()->willReturn($category);

        $this->preRemove($event);
    }

    function it_throws_an_exception_if_the_category_is_linked_to_a_published_product(
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('categories', Operators::IN_CHILDREN_LIST, ['category_code'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(2);

        $category = new Category();
        $category->setCode('category_code');
        $event->getSubject()->willReturn($category);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove category linked to a published product')
            )
            ->duringPreRemove($event);
    }

    function it_checks_if_the_group_is_linked_to_a_published_product(
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('groups', Operators::IN_LIST, ['group_code'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(0);

        $category = new Group();
        $category->setCode('group_code');
        $event->getSubject()->willReturn($category);

        $this->preRemove($event);
    }

    function it_throws_an_exception_if_the_group_is_linked_to_a_published_product(
        $queryBuilderFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $productCursor
    ) {
        $queryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('groups', Operators::IN_LIST, ['group_code'])->willReturn($pqb);
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(1);

        $category = new Group();
        $category->setCode('group_code');
        $event->getSubject()->willReturn($category);

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
