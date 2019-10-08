<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave\ComputeProductAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetDescendantVariantProductIdentifiers;
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
class ComputeProductAndAncestorsSubscriberSpec extends ObjectBehavior
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
        $this->shouldHaveType(ComputeProductAndAncestorsSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_product_model_save_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE => 'onProductModelSave',
            StorageEvents::POST_SAVE_ALL => 'onProductModelSaveAll',
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

        $this->onProductModelSave($event);
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

        $this->onProductModelSave($event);
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

        $this->onProductModelSave($event);
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

        $this->onProductModelSave($event);
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

        $this->onProductModelSaveAll($event);
    }

    function it_does_not_bulk_compute_and_index_if_not_product_models_event(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $event = new GenericEvent([new \stdClass(), new \stdClass()]);

        $getDescendantVariantProductIdentifiers->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $this->onProductModelSaveAll($event);
    }
}
