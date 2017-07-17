<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Product saver, define custom logic and options for product saving
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityWithValuesSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var EntityWithValuesUniqueDataSynchronizer */
    protected $uniqueDataSynchronizer;

    /**
     * @param ObjectManager                          $objectManager
     * @param EventDispatcherInterface               $eventDispatcher
     * @param EntityWithValuesUniqueDataSynchronizer $uniqueDataSynchronizer
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        EntityWithValuesUniqueDataSynchronizer $uniqueDataSynchronizer
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->uniqueDataSynchronizer = $uniqueDataSynchronizer;
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity, array $options = [])
    {
        $this->validateProduct($entity);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($entity, $options));

        // TODO REMOVE THAT IN PIM-6448-validation
        if ($entity instanceof ProductInterface) {
            $this->uniqueDataSynchronizer->synchronize($entity);
        }

        $this->objectManager->persist($entity);
        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($entity, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {
            $this->validateProduct($product);
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($products, $options));

        foreach ($products as $product) {
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));
            $this->uniqueDataSynchronizer->synchronize($product);

            $this->objectManager->persist($product);
        }

        $this->objectManager->flush();

        foreach ($products as $product) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($products, $options));
    }

    /**
     * @param $product
     */
    protected function validateProduct($product)
    {
        if (!$product instanceof EntityWithValuesInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    EntityWithValuesInterface::class,
                    ClassUtils::getClass($product)
                )
            );
        }
    }
}
