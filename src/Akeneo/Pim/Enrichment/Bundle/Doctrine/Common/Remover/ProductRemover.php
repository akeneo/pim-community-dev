<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductRemover implements RemoverInterface, BulkRemoverInterface
{
    public function __construct(
        private ObjectManager $objectManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function remove($product, array $options = [])
    {
        Assert::implementsInterface($product, ProductInterface::class);

        $productUuid = $product->getUuid();

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(new RemoveEvent($product, $productUuid, $options), StorageEvents::PRE_REMOVE);

        $this->objectManager->remove($product);
        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(new RemoveEvent($product, $productUuid, $options), StorageEvents::POST_REMOVE);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new RemoveEvent($products, null), StorageEvents::PRE_REMOVE_ALL);

        foreach ($products as $product) {
            Assert::implementsInterface($product, ProductInterface::class);

            $this->eventDispatcher->dispatch(
                new RemoveEvent($product, $product->getUuid(), $options),
                StorageEvents::PRE_REMOVE
            );
        }

        $removedProducts = [];
        foreach ($products as $object) {
            $removedProducts[$object->getUuid()->toString()] = $object;
            $this->objectManager->remove($object);
        }

        $this->objectManager->flush();

        foreach ($removedProducts as $uuid => $object) {
            $this->eventDispatcher->dispatch(
                new RemoveEvent($object, Uuid::fromString($uuid), $options),
                StorageEvents::POST_REMOVE
            );
        }

        $this->eventDispatcher->dispatch(
            new RemoveEvent(
                $products,
                array_map(fn (string $uuid): UuidInterface => Uuid::fromString($uuid), array_keys($removedProducts))
            ),
            StorageEvents::POST_REMOVE_ALL
        );
    }
}
