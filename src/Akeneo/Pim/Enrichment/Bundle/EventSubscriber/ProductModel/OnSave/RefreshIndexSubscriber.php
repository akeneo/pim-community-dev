<?php


namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshIndexSubscriber implements EventSubscriberInterface
{
    private Client $productAndProductModelClient;

    public function __construct(Client $productAndProductModelClient)
    {
        $this->productAndProductModelClient = $productAndProductModelClient;
    }

    /**
     * When a product model is created we want to ensure its availability in Elasticsearch as soon as possible
     * so that we refresh the index in priority
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['refresh', 20],
        ];
    }

    public function refresh(GenericEvent $event): void
    {
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (!$event->hasArgument('is_new') || false === $event->getArgument('is_new')) {
            return;
        }

        $this->productAndProductModelClient->refreshIndex();
    }
}
