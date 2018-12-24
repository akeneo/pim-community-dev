<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ProductToVariantProductSubscriber implements EventSubscriberInterface
{
    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /**
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     */
    public function __construct(UnsubscribeProductHandler $unsubscribeProductHandler)
    {
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'onPostSave',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostSave(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$product->isVariant()) {
            return;
        }

        $this->unsubscribeProduct($product->getId());
    }

    /**
     * @param int $productId
     */
    private function unsubscribeProduct(int $productId): void
    {
        try {
            $command = new UnsubscribeProductCommand($productId);
            $this->unsubscribeProductHandler->handle($command);
        } catch (ProductNotSubscribedException $e) {
            // Silently catch exception if the product is not subscribed
            // We don't check it here as the handler already check it. No need to do it twice
            return;
        }
    }
}
