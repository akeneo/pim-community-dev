<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs as MongoDBODMLifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs as MongoDBODMPreUpdateEventsArgs;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\ORM\Event\LifecycleEventArgs as ORMLifecycleEventsArgs;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class SynchronizeProductDraftCategoriesSubscriberSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, ProductDraftRepositoryInterface $repository)
    {
        $this->beConstructedWith($registry, 'ProductDraftClassName');

        $registry->getRepository('ProductDraftClassName')->willReturn($repository);
    }

    function it_is_a_doctrine_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_some_doctrine_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'prePersist',
            'preUpdate',
            'preRemove',
        ]);
    }

    function it_synchronizes_product_draft_document_before_it_is_persisted(
        MongoDBODMLifecycleEventArgs $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        CategoryInterface $catA,
        CategoryInterface $catB
    ) {
        $event->getDocument()->willReturn($productDraft);
        $productDraft->getProduct()->willReturn($product);
        $product
            ->getCategories()
            ->willReturn(new ArrayCollection([$catA->getWrappedObject(), $catB->getWrappedObject()]));
        $catA->getId()->willReturn(4);
        $catB->getId()->willReturn(8);

        $productDraft->setCategoryIds([4, 8])->shouldBeCalled();

        $this->prePersist($event);
    }

    function it_synchronizes_product_draft_document_before_it_is_updated(
        MongoDBODMPreUpdateEventsArgs $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        CategoryInterface $catA,
        CategoryInterface $catB
    ) {
        $event->getDocument()->willReturn($productDraft);
        $productDraft->getProduct()->willReturn($product);
        $product
            ->getCategories()
            ->willReturn(new ArrayCollection([$catA->getWrappedObject(), $catB->getWrappedObject()]));
        $catA->getId()->willReturn(4);
        $catB->getId()->willReturn(8);

        $productDraft->setCategoryIds([4, 8])->shouldBeCalled();

        $this->preUpdate($event);
    }

    function it_synchronizes_product_product_draft_documents_when_its_category_is_updated(
        $repository,
        MongoDBODMPreUpdateEventsArgs $event,
        ProductInterface $product,
        CategoryInterface $catA,
        CategoryInterface $catB,
        ProductDraftInterface $productDraftA,
        ProductDraftInterface $productDraftB,
        DocumentManager $dm,
        UnitOfWork $uow
    ) {
        $event->getDocument()->willReturn($product);
        $event->hasChangedField('categoryIds')->willReturn(true);
        $event->getDocumentManager()->willReturn($dm);
        $dm->getUnitOfWork()->willReturn($uow);

        $product
            ->getCategories()
            ->willReturn(new ArrayCollection([$catA->getWrappedObject(), $catB->getWrappedObject()]));
        $catA->getId()->willReturn(4);
        $catB->getId()->willReturn(8);

        $repository->findByProduct($product)->willReturn([$productDraftA, $productDraftB]);
        $productDraftA->getCategoryIds()->willReturn([]);
        $productDraftB->getCategoryIds()->willReturn([15, 16]);

        $uow->scheduleExtraUpdate($productDraftA, ['categoryIds' => [[      ], [4, 8]]])->shouldBeCalled();
        $uow->scheduleExtraUpdate($productDraftB, ['categoryIds' => [[15, 16], [4, 8]]])->shouldBeCalled();

        $this->preUpdate($event);
    }

    function it_synchronizes_product_belonging_to_a_category_before_it_is_removed(
        $repository,
        ORMLifecycleEventsArgs $event,
        CategoryInterface $category,
        ProductInterface $productA,
        ProductInterface $productB,
        ProductDraftInterface $productDraftAA,
        ProductDraftInterface $productDraftAB,
        ProductDraftInterface $productDraftBA
    ) {
        $event->getEntity()->willReturn($category);
        $category->getId()->willReturn(4);
        $category->getProducts()->willReturn([$productA, $productB]);

        $repository->findByProduct($productA)->willReturn([$productDraftAA, $productDraftAB]);
        $repository->findByProduct($productB)->willReturn([$productDraftBA]);

        $productDraftAA->removeCategoryId(4)->shouldBeCalled();
        $productDraftAB->removeCategoryId(4)->shouldBeCalled();
        $productDraftBA->removeCategoryId(4)->shouldBeCalled();

        $this->preRemove($event);
    }

    function it_does_not_synchronize_before_persisting_if_event_is_not_from_mongo(
        LifecycleEventArgs $event
    ) {
        $event->getObject()->shouldNotBeCalled();

        $this->prePersist($event);
    }

    function it_does_not_synchronize_before_updating_if_event_is_not_from_mongo(
        LifecycleEventArgs $event
    ) {
        $event->getObject()->shouldNotBeCalled();

        $this->preUpdate($event);
    }

    function it_does_not_synchronize_before_removing_if_event_is_not_from_orm(
        LifecycleEventArgs $event
    ) {
        $event->getObject()->shouldNotBeCalled();

        $this->preRemove($event);
    }

    function it_does_not_synchronize_product_product_draft_documents_when_its_category_has_not_been_updated(
        $repository,
        MongoDBODMPreUpdateEventsArgs $event,
        ProductInterface $product,
        CategoryInterface $catA,
        CategoryInterface $catB,
        ProductDraftInterface $productDraft,
        DocumentManager $dm,
        UnitOfWork $uow
    ) {
        $event->getDocument()->willReturn($product);
        $event->hasChangedField('categoryIds')->willReturn(false);
        $event->getDocumentManager()->willReturn($dm);
        $dm->getUnitOfWork()->willReturn($uow);

        $product
            ->getCategories()
            ->willReturn(new ArrayCollection([$catA->getWrappedObject(), $catB->getWrappedObject()]));
        $catA->getId()->willReturn(4);
        $catB->getId()->willReturn(8);

        $repository->findByProduct($product)->willReturn([$productDraft]);

        $uow->scheduleExtraUpdate(Argument::cetera())->shouldNotBeCalled();

        $this->preUpdate($event);
    }
}
