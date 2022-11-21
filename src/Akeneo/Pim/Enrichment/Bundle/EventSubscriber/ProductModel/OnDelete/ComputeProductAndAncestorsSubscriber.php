<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
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
    public function __construct(
        private ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        private Client $esClient
    ) {
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

        if ($this->elasticsearchShouldBeRefreshed([$productModel])) {
            $this->esClient->refreshIndex();
        }

        $this->productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds([$event->getSubjectId()]);
    }

    public function onProductModelRemoveAll(RemoveEvent $event): void
    {
        $productModels = $event->getSubject();
        if (!is_array($productModels) || !current($productModels) instanceof ProductModelInterface) {
            return;
        }

        if ($this->elasticsearchShouldBeRefreshed($productModels)) {
            $this->esClient->refreshIndex();
        }

        $this->productModelDescendantsAndAncestorsIndexer->removeFromProductModelIds($event->getSubjectId());
    }

    /**
     * PIM-10467: If a product model is created then deleted very quickly, ES can not have the PM yet because
     * it is refreshed every second. We take 10 seconds by security in case of overload.
     *
     * @param ProductModelInterface[] $productModels
     * @return bool
     */
    private function elasticsearchShouldBeRefreshed(array $productModels): bool
    {
        foreach ($productModels as $productModel) {
            if (null !== $productModel->getCreated() && 10 > time() - $productModel->getCreated()->getTimestamp()) {
                return true;
            }
        }

        return false;
    }
}
