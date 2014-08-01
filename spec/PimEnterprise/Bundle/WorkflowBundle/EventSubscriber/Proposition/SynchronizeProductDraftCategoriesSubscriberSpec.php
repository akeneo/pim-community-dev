<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs as MongoDBODMLifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs as MongoDBODMPreUpdateEventsArgs;
use Doctrine\ORM\Event\LifecycleEventArgs as ORMLifecycleEventsArgs;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class SynchronizeProductDraftCategoriesSubscriberSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, ProductDraftRepositoryInterface $repository)
    {
        $this->beConstructedWith($registry, 'PropositionClassName');

        $registry->getRepository('PropositionClassName')->willReturn($repository);
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

    function it_synchronizes_proposition_document_before_it_is_persisted(
        MongoDBODMLifecycleEventArgs $event,
        Proposition $proposition,
        ProductInterface $product,
        CategoryInterface $catA,
        CategoryInterface $catB
    ) {
        $event->getDocument()->willReturn($proposition);
        $proposition->getProduct()->willReturn($product);
        $product->getCategories()->willReturn(new ArrayCollection([$catA->getWrappedObject(), $catB->getWrappedObject()]));
        $catA->getId()->willReturn(4);
        $catB->getId()->willReturn(8);

        $proposition->setCategoryIds([4, 8])->shouldBeCalled();

        $this->prePersist($event);
    }

    function it_synchronizes_proposition_document_before_it_is_updated(
        MongoDBODMPreUpdateEventsArgs $event,
        Proposition $proposition,
        ProductInterface $product,
        CategoryInterface $catA,
        CategoryInterface $catB
    ) {
        $event->getDocument()->willReturn($proposition);
        $proposition->getProduct()->willReturn($product);
        $product->getCategories()->willReturn(new ArrayCollection([$catA->getWrappedObject(), $catB->getWrappedObject()]));
        $catA->getId()->willReturn(4);
        $catB->getId()->willReturn(8);

        $proposition->setCategoryIds([4, 8])->shouldBeCalled();

        $this->preUpdate($event);
    }

    function it_synchronizes_product_proposition_documents_when_its_category_is_updated(
        $repository,
        MongoDBODMPreUpdateEventsArgs $event,
        ProductInterface $product,
        CategoryInterface $catA,
        CategoryInterface $catB,
        Proposition $propositionA,
        Proposition $propositionB,
        DocumentManager $dm,
        UnitOfWork $uow
    ) {
        $event->getDocument()->willReturn($product);
        $event->hasChangedField('categoryIds')->willReturn(true);
        $event->getDocumentManager()->willReturn($dm);
        $dm->getUnitOfWork()->willReturn($uow);

        $product->getCategories()->willReturn(new ArrayCollection([$catA->getWrappedObject(), $catB->getWrappedObject()]));
        $catA->getId()->willReturn(4);
        $catB->getId()->willReturn(8);

        $repository->findByProduct($product)->willReturn([$propositionA, $propositionB]);
        $propositionA->getCategoryIds()->willReturn([]);
        $propositionB->getCategoryIds()->willReturn([15, 16]);

        $uow->scheduleExtraUpdate($propositionA, ['categoryIds' => [[      ], [4, 8]]])->shouldBeCalled();
        $uow->scheduleExtraUpdate($propositionB, ['categoryIds' => [[15, 16], [4, 8]]])->shouldBeCalled();

        $this->preUpdate($event);
    }

    function it_synchronizes_product_belonging_to_a_category_before_it_is_removed(
        $repository,
        ORMLifecycleEventsArgs $event,
        CategoryInterface $category,
        ProductInterface $productA,
        ProductInterface $productB,
        Proposition $propositionAA,
        Proposition $propositionAB,
        Proposition $propositionBA
    ) {
        $event->getEntity()->willReturn($category);
        $category->getId()->willReturn(4);
        $category->getProducts()->willReturn([$productA, $productB]);

        $repository->findByProduct($productA)->willReturn([$propositionAA, $propositionAB]);
        $repository->findByProduct($productB)->willReturn([$propositionBA]);

        $propositionAA->removeCategoryId(4)->shouldBeCalled();
        $propositionAB->removeCategoryId(4)->shouldBeCalled();
        $propositionBA->removeCategoryId(4)->shouldBeCalled();

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

    function it_does_not_synchronize_product_proposition_documents_when_its_category_has_not_been_updated(
        $repository,
        MongoDBODMPreUpdateEventsArgs $event,
        ProductInterface $product,
        CategoryInterface $catA,
        CategoryInterface $catB,
        Proposition $proposition,
        DocumentManager $dm,
        UnitOfWork $uow
    ) {
        $event->getDocument()->willReturn($product);
        $event->hasChangedField('categoryIds')->willReturn(false);
        $event->getDocumentManager()->willReturn($dm);
        $dm->getUnitOfWork()->willReturn($uow);

        $product->getCategories()->willReturn(new ArrayCollection([$catA->getWrappedObject(), $catB->getWrappedObject()]));
        $catA->getId()->willReturn(4);
        $catB->getId()->willReturn(8);

        $repository->findByProduct($product)->willReturn([$proposition]);

        $uow->scheduleExtraUpdate(Argument::cetera())->shouldNotBeCalled();

        $this->preUpdate($event);
    }
}
