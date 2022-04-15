<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\PublishedProductConsistencyException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckPublishedProductOnRemovalSubscriberSpec extends ObjectBehavior
{
    private const PRODUCT_ERROR_MESSAGE = 'Impossible to remove a published product';
    private const PRODUCT_MODEL_ERROR_MESSAGE = 'This product model has a variant product that has been published. Please, unpublish it and try again';
    private const ENTITY_ERROR_MESSAGE = 'Entities linked to published products cannot be removed';

    function let(
        PublishedProductRepositoryInterface $publishedRepository,
        ProductQueryBuilderFactoryInterface $queryBuilderFactory,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        TranslatorInterface $translator
    ) {
        $translator->trans('pimee_workflow.check_removal.product_model_error')->willReturn(self::PRODUCT_MODEL_ERROR_MESSAGE);
        $translator->trans('pimee_workflow.check_removal.product_error')->willReturn(self::PRODUCT_ERROR_MESSAGE);
        $translator->trans('pimee_workflow.check_removal.entity_error')->willReturn(self::ENTITY_ERROR_MESSAGE);

        $this->beConstructedWith($publishedRepository, $queryBuilderFactory, $channelRepository, $localeRepository, $translator);
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
            ->shouldThrow(new PublishedProductConsistencyException(self::PRODUCT_ERROR_MESSAGE))
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
        $family->setCode('family_code');
        $event->getSubject()->willReturn($family);

        $this
            ->shouldNotThrow(PublishedProductConsistencyException::class)
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
            ->shouldThrow(new PublishedProductConsistencyException(self::ENTITY_ERROR_MESSAGE))
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
        $attribute->setCode('attribute_code');
        $event->getSubject()->willReturn($attribute);

        $this
            ->shouldNotThrow(PublishedProductConsistencyException::class)
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
            ->shouldThrow(new PublishedProductConsistencyException(self::ENTITY_ERROR_MESSAGE))
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
            ->shouldNotThrow(PublishedProductConsistencyException::class)
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
            ->shouldThrow(new PublishedProductConsistencyException(self::ENTITY_ERROR_MESSAGE))
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
            ->shouldThrow(new PublishedProductConsistencyException(self::ENTITY_ERROR_MESSAGE))
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
                new PublishedProductConsistencyException(self::ENTITY_ERROR_MESSAGE)
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
            ->shouldThrow(new PublishedProductConsistencyException(self::ENTITY_ERROR_MESSAGE))
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
            ->shouldThrow(new PublishedProductConsistencyException(self::ENTITY_ERROR_MESSAGE))
            ->duringPreRemove($event);
    }
}
