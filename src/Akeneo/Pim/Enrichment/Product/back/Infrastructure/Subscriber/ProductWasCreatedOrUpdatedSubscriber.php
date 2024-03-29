<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Subscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductWasCreatedOrUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var array<string, bool>  */
    private $createdProductsByUuid = [];

    /**
     * @param int<1, max> $batchSize
     */
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        private readonly ?string $tenantId,
        private readonly string $env,
        private readonly FeatureFlag $featureFlag,
        private readonly int $batchSize = 100
    ) {
        Assert::greaterThanEq($this->batchSize, 1);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'recordCreatedProduct',
            StorageEvents::PRE_SAVE_ALL => 'recordCreatedProducts',
            StorageEvents::POST_SAVE => 'dispatchProductWasUpdatedMessage',
            StorageEvents::POST_SAVE_ALL => 'dispatchProductWereUpdatedMessage',
        ];
    }

    public function recordCreatedProduct(GenericEvent $event): void
    {
        $product = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if (false === $unitary
            || !$product instanceof ProductInterface
            || \get_class($product) === 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
            || $this->isProdLegacy()
            || null !== $product->getCreated()
            || !$this->featureFlag->isEnabled()
        ) {
            return;
        }

        $this->createdProductsByUuid[$product->getUuid()->toString()] = true;
    }

    public function dispatchProductWasUpdatedMessage(GenericEvent $event): void
    {
        $product = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if (false === $unitary
            || !$product instanceof ProductInterface
            || \get_class($product) === 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
            || $this->isProdLegacy()
            || !$this->featureFlag->isEnabled()
        ) {
            return;
        }

        try {
            $updatedAt = $product->getUpdated();
            Assert::isInstanceOf($updatedAt, \DateTime::class);
            $event = ($this->createdProductsByUuid[$product->getUuid()->toString()] ?? false)
                ? new ProductWasCreated($product->getUuid(), \DateTimeImmutable::createFromMutable($updatedAt))
                : new ProductWasUpdated($product->getUuid(), \DateTimeImmutable::createFromMutable($updatedAt))
            ;
            unset($this->createdProductsByUuid[$product->getUuid()->toString()]);

            // Launch ProductsWereUpdatedMessage with a single event to simplify the messaging stack
            $this->messageBus->dispatch(new ProductsWereCreatedOrUpdated([$event]));
        } catch (\Throwable $exception) {
            // Catch any error to not block the critical path
            $this->logger->error('Failed to dispatch ProductsWereUpdatedMessage from unitary product update', [
                'product_uuid' => $product->getUuid()->toString(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function recordCreatedProducts(GenericEvent $event): void
    {
        $products = $event->getSubject();
        if (!\is_array($products)
            || [] === $products
            || !\reset($products) instanceof ProductInterface
            || \get_class(\reset($products)) === 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
            || $this->isProdLegacy()
            || !$this->featureFlag->isEnabled()
        ) {
            return;
        }

        foreach ($products as $product) {
            if (null === $product->getCreated()) {
                $this->createdProductsByUuid[(string) $product->getUuid()->toString()] = true;
            }
        }
    }

    public function dispatchProductWereUpdatedMessage(GenericEvent $event): void
    {
        $products = $event->getSubject();
        if (!\is_array($products)
            || [] === $products
            || !\reset($products) instanceof ProductInterface
            || \get_class(\reset($products)) === 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
            || $this->isProdLegacy()
            || !$this->featureFlag->isEnabled()
        ) {
            return;
        }

        $events = \array_map(
            function (ProductInterface $product) {
                $updatedAt = $product->getUpdated();
                Assert::isInstanceOf($updatedAt, \DateTime::class);
                $event = ($this->createdProductsByUuid[$product->getUuid()->toString()] ?? false)
                    ? new ProductWasCreated($product->getUuid(), \DateTimeImmutable::createFromMutable($updatedAt))
                    : new ProductWasUpdated($product->getUuid(), \DateTimeImmutable::createFromMutable($updatedAt))
                ;
                unset($this->createdProductsByUuid[$product->getUuid()->toString()]);

                return $event;
            },
            $products
        );
        $batchEvents = \array_chunk($events, $this->batchSize);

        try {
            foreach ($batchEvents as $events) {
                $this->messageBus->dispatch(new ProductsWereCreatedOrUpdated($events));
            }
        } catch (\Throwable $exception) {
            // Catch any error to not block the critical path
            $this->logger->error('Failed to dispatch ProductsWereUpdatedMessage from batch products update', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * In prod legacy we don't have pubsub topic and subscription, so it does not work.
     */
    private function isProdLegacy(): bool
    {
        return 'prod' === $this->env && null === $this->tenantId;
    }
}
