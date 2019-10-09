<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\BatchEventSubscriberInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class BatchOnSaveProductEventSubscriber implements BatchEventSubscriberInterface
{
    const BATCH_SIZE = 1000;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var bool */
    private $isActivated = false;

    /** @var array */
    private $eventProducts = [];

    /**
     * BatchOnSaveProductEvent constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getEventProducts(): array
    {
        return $this->eventProducts;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Priority must be high in order to catch events and stop propagation!
            StorageEvents::POST_SAVE => ['batchEvents', 10000],
        ];
    }

    public function activate(): void
    {
        $this->isActivated = true;
    }

    public function deactivate(): void
    {
        $this->isActivated = false;
    }

    public function batchEvents(GenericEvent $event)
    {
        $product = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;
        if (!$this->isActivated || !$product instanceof ProductInterface || !$unitary) {
            return;
        }

        $this->eventProducts[$product->getId()] = $product;

        // We don't stop propagation because subscribers may not handle the bulk save.
        $event->setArgument('unitary', false);

        if (count($this->eventProducts) >= self::BATCH_SIZE) {
            $this->dispatchAllEvents();
        }
    }

    public function dispatchAllEvents(): void
    {
        if (empty($this->eventProducts)) {
            return;
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($this->eventProducts));
        $this->eventProducts = [];
    }
}
