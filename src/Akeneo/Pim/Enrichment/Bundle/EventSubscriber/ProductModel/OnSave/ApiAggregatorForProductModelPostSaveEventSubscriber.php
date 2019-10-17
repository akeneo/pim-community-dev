<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * When activated, the goal of this subscriber is to catch every POST_SAVE events for product models,
 * and then dispatch a single POST_SAVE_ALL event with all saved product models.
 * This subscriber is deactivated by default.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ApiAggregatorForProductModelPostSaveEventSubscriber implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var bool */
    private $isActivated = false;

    /** @var array */
    private $eventProductModels = [];

    /**
     * BatchOnSaveProductEvent constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getEventProductModels(): array
    {
        return $this->eventProductModels;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Priority must be high in order to catch events before any other subscribers.
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
        $productModel = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;
        if (!$this->isActivated || !$productModel instanceof ProductModelInterface || !$unitary) {
            return;
        }

        $this->eventProductModels[$productModel->getCode()] = $productModel;

        // We don't stop propagation because subscribers may not handle the bulk save.
        $event->setArgument('unitary', false);
    }

    public function dispatchAllEvents(): void
    {
        if (empty($this->eventProductModels)) {
            return;
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($this->eventProductModels));
        $this->eventProductModels = [];
    }
}
