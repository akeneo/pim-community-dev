<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeDescendantCompletenessAndIndexProductAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantVariantProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeDescendantCompletenessAndIndexProductAndAncestorsSubscriberSpec extends ObjectBehavior
{
    public function let(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $this->beConstructedWith(
            $computeAndPersistProductCompletenesses,
            $productModelDescendantsAndAncestorsIndexer,
            $getDescendantVariantProductIdentifiers
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeDescendantCompletenessAndIndexProductAndAncestorsSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_product_model_save_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE     => 'fromProductModelEvent',
            StorageEvents::POST_SAVE_ALL => 'fromProductModelsEvent',
            StorageEvents::POST_REMOVE     => 'fromProductModelRemoveEvent',
            StorageEvents::POST_REMOVE_ALL => 'fromProductModelsRemoveEvent',
        ]);
    }

    function it_computes_variant_products_and_indexes(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        ProductModelInterface $productModel
    ) {
        $event = new GenericEvent($productModel->getWrappedObject(), ['unitary' => true]);
        $productModel->getCode()->willReturn('pm');
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm'])->willReturn(['p1', 'p2']);

        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['p1', 'p2'])->shouldBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(['pm'])->shouldBeCalled();

        $this->fromProductModelEvent($event);
    }

    function it_just_indexes_if_no_variant_products(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        ProductModelInterface $productModel
    ) {
        $event = new GenericEvent($productModel->getWrappedObject(), ['unitary' => true]);
        $productModel->getCode()->willReturn('pm');
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm'])->willReturn([]);

        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(['pm'])->shouldBeCalled();

        $this->fromProductModelEvent($event);
    }

    function it_does_not_compute_and_index_if_not_unitary(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        ProductModelInterface $productModel
    ) {
        $event = new GenericEvent($productModel->getWrappedObject(), ['unitary' => false]);

        $getDescendantVariantProductIdentifiers->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductModelEvent($event);
    }

    function it_does_not_compute_and_index_if_not_product_model_event(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $event = new GenericEvent(new \stdClass(), ['unitary' => true]);

        $getDescendantVariantProductIdentifiers->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductModelEvent($event);
    }

    function it_computes_variant_products_and_indexes_from_product_models_event(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $event = new GenericEvent([$productModel1->getWrappedObject(), $productModel2->getWrappedObject()]);
        $productModel1->getCode()->willReturn('pm1');
        $productModel2->getCode()->willReturn('pm2');

        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm1', 'pm2'])->willReturn(['p1', 'p2']);
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['p1', 'p2'])->shouldBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(['pm1', 'pm2'])->shouldBeCalled();

        $this->fromProductModelsEvent($event);
    }

    function it_does_not_bulk_compute_and_index_if_not_product_models_event(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $event = new GenericEvent([new \stdClass(), new \stdClass()]);

        $getDescendantVariantProductIdentifiers->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductModelsEvent($event);
    }

    function it_removes_product_model_from_index(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        ProductModelInterface $productModel
    ) {
        $event = new RemoveEvent($productModel->getWrappedObject(), 12, ['unitary' => true]);
        $productModel->getId()->willReturn(12);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds([12])->shouldBeCalled();

        $this->fromProductModelRemoveEvent($event);
    }

    function it_does_not_remove_product_model_from_index_if_not_unitary(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        ProductModelInterface $productModel
    ) {
        $event = new RemoveEvent($productModel->getWrappedObject(), 12, ['unitary' => false]);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();
        $this->fromProductModelRemoveEvent($event);

        $event = new RemoveEvent($productModel->getWrappedObject(), 12);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();
        $this->fromProductModelRemoveEvent($event);
    }

    function it_does_not_remove_product_model_from_index_if_not_product_model(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        ProductModelInterface $productModel
    ) {
        $event = new RemoveEvent(new \stdClass(), 12, ['unitary' => true]);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductModelRemoveEvent($event);
    }

    function it_bulk_removes_product_models_from_index(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $event = new RemoveEvent([$productModel1->getWrappedObject(), $productModel2->getWrappedObject()], null);
        $productModel1->getId()->willReturn(12);
        $productModel2->getId()->willReturn(14);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds([12, 14])->shouldBeCalled();

        $this->fromProductModelsRemoveEvent($event);
    }

    function it_does_not_remove_product_model_from_index_if_empty(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        ProductModelInterface $productModel
    ) {
        $event = new RemoveEvent([], null);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductModelRemoveEvent($event);
    }
}
