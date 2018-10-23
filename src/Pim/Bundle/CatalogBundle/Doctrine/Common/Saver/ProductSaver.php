<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Manager\CompletenessManager;
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
class ProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductUniqueDataSynchronizer */
    protected $uniqueDataSynchronizer;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @param ObjectManager                 $objectManager
     * @param CompletenessManager           $completenessManager
     * @param EventDispatcherInterface      $eventDispatcher
     * @param ProductUniqueDataSynchronizer $uniqueDataSynchronizer
     * @param EntityManagerInterface        $entityManager
     */
    public function __construct(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        EntityManagerInterface $entityManager
    ) {
        $this->objectManager = $objectManager;
        $this->completenessManager = $completenessManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->uniqueDataSynchronizer = $uniqueDataSynchronizer;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        $this->validateProduct($product);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));

        $this->uniqueDataSynchronizer->synchronize($product);
        $this->completenessManager->schedule($product);

        $this->objectManager->persist($product);
        $this->objectManager->flush();

        $this->completenessManager->generateMissingForProduct($product);
        $this->persistProductCompleteness($product);

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $products = array_unique($products, SORT_REGULAR);

        foreach ($products as $product) {
            $this->validateProduct($product);
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($products, $options));

        foreach ($products as $product) {
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));
            $this->uniqueDataSynchronizer->synchronize($product);
            $this->completenessManager->schedule($product);
            $this->objectManager->persist($product);
        }

        $this->objectManager->flush();

        foreach ($products as $product) {
            $this->completenessManager->generateMissingForProduct($product);
            $this->persistProductCompleteness($product);
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($products, $options));
    }

    /**
     * @param $product
     */
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

    private function persistProductCompleteness(ProductInterface $product)
    {
        $connection = $this->entityManager->getConnection();

        $query = 'INSERT INTO pim_catalog_completeness 
                  SET locale_id = %1$d, channel_id = %2$d, product_id = %3$d, ratio = %4$d, missing_count = %5$d, required_count = %6$d
                  ON DUPLICATE KEY UPDATE ratio = %4$d';

        foreach ($product->getCompletenesses() as $completeness) {
            $queryWithValues = sprintf(
                $query,
                $completeness->getLocale()->getId(),
                $completeness->getChannel()->getId(),
                $product->getId(),
                $completeness->getRatio(),
                $completeness->getMissingCount(),
                $completeness->getRequiredCount()
            );
            $connection->query($queryWithValues);
        }
    }
}
