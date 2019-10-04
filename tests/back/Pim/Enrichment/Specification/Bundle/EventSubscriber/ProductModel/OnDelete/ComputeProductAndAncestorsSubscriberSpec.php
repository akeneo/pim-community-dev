<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnDelete\ComputeProductAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeProductAndAncestorsSubscriberSpec extends ObjectBehavior
{
    public function let(ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer)
    {
        $this->beConstructedWith($productModelDescendantsAndAncestorsIndexer);
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
            StorageEvents::POST_REMOVE => 'onProductModelRemove',
            StorageEvents::POST_REMOVE_ALL => 'onProductModelRemoveAll',
        ]);
    }

    function it_removes_product_model_from_index(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        ProductModelInterface $productModel
    ) {
        $event = new RemoveEvent($productModel->getWrappedObject(), 12, ['unitary' => true]);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds([12])->shouldBeCalled();

        $this->onProductModelRemove($event);
    }

    function it_does_not_remove_product_model_from_index_if_not_unitary(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        ProductModelInterface $productModel
    ) {
        $event = new RemoveEvent($productModel->getWrappedObject(), 12, ['unitary' => false]);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();
        $this->onProductModelRemove($event);

        $event = new RemoveEvent($productModel->getWrappedObject(), 12);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();
        $this->onProductModelRemove($event);
    }

    function it_does_not_remove_product_model_from_index_if_not_product_model(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer
    ) {
        $event = new RemoveEvent(new \stdClass(), 12, ['unitary' => true]);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();

        $this->onProductModelRemove($event);
    }

    function it_bulk_removes_product_models_from_index(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $event = new RemoveEvent([$productModel1->getWrappedObject(), $productModel2->getWrappedObject()], [12, 14]);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds([12, 14])->shouldBeCalled();

        $this->onProductModelRemoveAll($event);
    }

    function it_does_not_remove_product_model_from_index_if_empty(
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer
    ) {
        $event = new RemoveEvent([], null);
        $productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();

        $this->onProductModelRemove($event);
    }
}
