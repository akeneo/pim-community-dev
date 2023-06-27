<?php

declare(strict_types=1);


/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasCreated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductModelWasCreatedOrUpdatedSubscriber implements EventSubscriberInterface
{
    /**
     * @var array<string, bool>
     */
    private array $createdProductModelsByCode;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        private readonly ?string $tenantId,
        private readonly string $env,
        private readonly int $batchSize = 100,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'recordCreatedProductModel',
            StorageEvents::PRE_SAVE_ALL => 'recordCreatedProductModels',
            StorageEvents::POST_SAVE => 'dispatchProductModelWasUpdatedMessage',
            StorageEvents::POST_SAVE_ALL => 'dispatchProductModelsWereUpdatedMessage',
        ];
    }

    private function recordCreatedProductModel(GenericEvent $event): void
    {
        $productModel = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if (false === $unitary
            || !$productModel instanceof ProductModelInterface
            || $this->isProdLegacy()
        ) {
            return;
        }

        if (null === $productModel->getCreated()) {
            $this->createdProductModelsByCode[$productModel->getCode()] = true;
        }
    }

    private function recordCreatedProductModels(GenericEvent $event): void
    {
        $productModels = $event->getSubject();
        if (empty($productModels)
            || !reset($productModels) instanceof ProductModelInterface
            || $this->isProdLegacy()
        ) {
            return;
        }

        foreach ($productModels as $productModel) {
            if (null === $productModel->getCreated()) {
                $this->createdProductModelsByCode[$productModel->getCode()] = true;
            }
        }
    }

    private function dispatchProductModelWasUpdatedMessage(GenericEvent $event): void
    {
        $productModel = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if (false === $unitary
            || !$productModel instanceof ProductModelInterface
            || $this->isProdLegacy()
        ) {
            return;
        }

        try {
            $event = ($this->createdProductModelsByCode[$productModel->getCode()] ?? false)
                ? new ProductModelWasCreated($productModel->getId(), \DateTimeImmutable::createFromMutable($productModel->getCreated()))
                : new ProductModelWasUpdated($productModel->getId(), \DateTimeImmutable::createFromMutable($productModel->getUpdated()))
            ;
            unset($this->createdProductModelsByCode[$productModel->getCode()]);

            $this->messageBus->dispatch(new ProductModelsWereCreatedOrUpdated([$event]));
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to dispatch ProductsWereUpdatedMessage from unitary product update', [
                'product_model_code' => $productModel->getCode(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function dispatchProductModelsWereUpdatedMessage(GenericEvent $event): void
    {
        $productModels = $event->getSubject();
        if (empty($productModels)
            || !reset($productModels) instanceof ProductModelInterface
            || $this->isProdLegacy()
        ) {
            return;
        }

        try {
            $events = \array_map(
                function (ProductModelInterface $productModel) {
                    $event = ($this->createdProductModelsByCode[$productModel->getCode()] ?? false)
                        ? new ProductModelWasCreated($productModel->getCode(), \DateTimeImmutable::createFromMutable($productModel->getCreated()))
                        : new ProductModelWasUpdated($productModel->getCode(), \DateTimeImmutable::createFromMutable($productModel->getCreated()))
                    ;
                    unset($this->createdProductModelsByCode[$productModel->getCode()]);

                    return $event;
                },
                $productModels
            );

            $batchEvents = \array_chunk($events, $this->batchSize);
            foreach ($batchEvents as $events) {
                $message = new ProductModelsWereCreatedOrUpdated($events);
                $this->messageBus->dispatch($message);
            }
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to dispatch ProductModelsWereUpdatedMessage from batch products update', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * In prod legacy we don't have pubsub topic and subscription, so it would not work.
     *
     * @return bool
     */
    private function isProdLegacy(): bool
    {
        return 'prod' === $this->env && null === $this->tenantId;
    }
}


