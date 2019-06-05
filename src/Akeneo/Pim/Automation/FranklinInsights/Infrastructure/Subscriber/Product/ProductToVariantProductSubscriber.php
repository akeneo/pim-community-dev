<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
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

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /**
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     * @param GetConnectionStatusHandler $connectionStatusHandler
     */
    public function __construct(
        UnsubscribeProductHandler $unsubscribeProductHandler,
        GetConnectionStatusHandler $connectionStatusHandler
    ) {
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
        $this->connectionStatusHandler = $connectionStatusHandler;
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

        if (!$this->isFranklinInsightsActivated()) {
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
            $command = new UnsubscribeProductCommand(new ProductId($productId));
            $this->unsubscribeProductHandler->handle($command);
        } catch (ProductNotSubscribedException $e) {
            // Silently catch exception if the product is not subscribed
            // We don't check it here as the handler already check it. No need to do it twice
            return;
        }
    }

    /**
     * @return bool
     */
    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
