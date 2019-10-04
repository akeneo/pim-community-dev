<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * On product models delete:
 *   - Remove given product models and its descendants (product models and variant products)
 *   - Re-index ancestor if any
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductAndAncestorsSubscriber implements EventSubscriberInterface
{
    /** @var ProductModelDescendantsAndAncestorsIndexer */
    private $productModelDescendantsAndAncestorsIndexer;

    public function __construct(ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer)
    {
        $this->productModelDescendantsAndAncestorsIndexer = $productModelDescendantsAndAncestorsIndexer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'onProductModelRemove',
            StorageEvents::POST_REMOVE_ALL => 'onProductModelRemoveAll',
        ];
    }

    public function onProductModelRemove(RemoveEvent $event): void
    {
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds([$event->getSubjectId()]);
    }

    public function onProductModelRemoveAll(RemoveEvent $event): void
    {
        $productModels = $event->getSubject();
        if (!is_array($productModels) || !current($productModels) instanceof ProductModelInterface) {
            return;
        }

        $this->productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds($event->getSubjectId());
    }
}
