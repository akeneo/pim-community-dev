<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
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
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var CompletenessCalculatorInterface */
    protected $completenessCalculator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EntityManagerInterface          $entityManager
     * @param CompletenessCalculatorInterface $completenessCalculator
     * @param EventDispatcherInterface        $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CompletenessCalculatorInterface $completenessCalculator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->completenessCalculator = $completenessCalculator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        $this->validateProduct($product);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));

        $this->dropCompletenesses([$product]);
        $this->calculateProductCompletenesses($product);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

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

        foreach ($products as $product) {
            $this->validateProduct($product);
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($products, $options));

        $this->dropCompletenesses($products);

        foreach ($products as $product) {
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));

            $this->calculateProductCompletenesses($product);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();

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
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Component\Catalog\Model\ProductInterface, "%s" provided',
                    ClassUtils::getClass($product)
                )
            );
        }
    }

    /**
     * Drops the current completenesses from the database for a list of products.
     *
     * @param ProductInterface[] $products
     */
    protected function dropCompletenesses(array $products)
    {
        $completenessIDs = [];
        foreach ($products as $product) {
            $completenesses = $product->getCompletenesses();
            foreach ($completenesses as $completeness) {
                $completenessIDs[] = $completeness->getId();
            }
        }

        $stmt = $this->entityManager->getConnection()->executeQuery(
            'DELETE c FROM pim_catalog_completeness c WHERE c.id IN (?)',
            [$completenessIDs],
            [Connection::PARAM_INT_ARRAY]
        );
        $stmt->execute();
    }

    /**
     * Calculates current product completenesses.
     *
     * The current completenesses collection is first cleared, then newly calculated ones are set to the product.
     *
     * @param ProductInterface $product
     */
    protected function calculateProductCompletenesses(ProductInterface $product)
    {
        $completenessesCollection = $product->getCompletenesses();

        if (!$completenessesCollection->isEmpty()) {
            $completenessesCollection->clear();
        }

        $newCompletenesses = $this->completenessCalculator->calculate($product);

        foreach ($newCompletenesses as $completeness) {
            $completenessesCollection->add($completeness);
        }
    }
}
