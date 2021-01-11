<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Product saver, define custom logic and options for product saving
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductUniqueDataSynchronizer */
    protected $uniqueDataSynchronizer;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->uniqueDataSynchronizer = $uniqueDataSynchronizer;
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        $this->validateProduct($product);
        if (!$product->isDirty() && true !== ($options['force_save'] ?? false)) {
            return;
        }

        $options['unitary'] = true;
        $options['is_new'] = null === $product->getId();

        $this->eventDispatcher->dispatch(new GenericEvent($product, $options), StorageEvents::PRE_SAVE);

        $this->uniqueDataSynchronizer->synchronize($product);

        $this->objectManager->persist($product);
        $this->objectManager->flush();

        $product->cleanup();

        $this->eventDispatcher->dispatch(new GenericEvent($product, $options), StorageEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        $products = array_unique($products, SORT_REGULAR);
        foreach ($products as $product) {
            $this->validateProduct($product);
        }

        if (true !== ($options['force_save'] ?? false)) {
            $products = array_values(
                array_filter(
                    $products,
                    function (ProductInterface $product): bool {
                        return $product->isDirty();
                    }
                )
            );
        }

        if (empty($products)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new GenericEvent($products, $options), StorageEvents::PRE_SAVE_ALL);

        $areProductsNew = array_map(function ($product) {
            return null === $product->getId();
        }, $products);

        foreach ($products as $i => $product) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($product, array_merge($options, ['is_new' => $areProductsNew[$i]])),
                StorageEvents::PRE_SAVE
            );
            $this->uniqueDataSynchronizer->synchronize($product);

            $this->objectManager->persist($product);
        }

        $this->objectManager->flush();

        foreach ($products as $i => $product) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($product, array_merge($options, ['is_new' => $areProductsNew[$i]])),
                StorageEvents::POST_SAVE
            );
        }

        $this->eventDispatcher->dispatch(
            new GenericEvent($products, $options),
            StorageEvents::POST_SAVE_ALL
        );

        foreach ($products as $product) {
            $product->cleanup();
        }
    }

    protected function validateProduct($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    ProductInterface::class,
                    ClassUtils::getClass($product)
                )
            );
        }
    }
}
