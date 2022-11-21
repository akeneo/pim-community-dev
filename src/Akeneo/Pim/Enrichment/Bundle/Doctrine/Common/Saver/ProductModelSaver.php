<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelSaver implements SaverInterface, BulkSaverInterface
{
    protected ObjectManager $objectManager;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($productModel, array $options = [])
    {
        $this->validateProductModel($productModel);
        if (!$productModel->isDirty() && true !== ($options['force_save'] ?? false)) {
            return;
        }

        $options['unitary'] = true;
        $options['is_new'] = null === $productModel->getId();

        $this->eventDispatcher->dispatch(new GenericEvent($productModel, $options), StorageEvents::PRE_SAVE);

        $this->objectManager->persist($productModel);
        $this->objectManager->flush();

        $productModel->cleanup();

        $this->eventDispatcher->dispatch(new GenericEvent($productModel, $options), StorageEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $productModels, array $options = [])
    {
        $productModels = array_unique($productModels, SORT_REGULAR);
        foreach ($productModels as $productModel) {
            $this->validateProductModel($productModel);
        }

        if (true !== ($options['force_save'] ?? false)) {
            $productModels = array_values(
                array_filter(
                    $productModels,
                    function (ProductModelInterface $product): bool {
                        return $product->isDirty();
                    }
                )
            );
        }

        if (empty($productModels)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new GenericEvent($productModels, $options), StorageEvents::PRE_SAVE_ALL);

        $areProductsNew = array_map(
            function ($productModel) {
                return null === $productModel->getId();
            },
            $productModels
        );

        foreach ($productModels as $i => $productModel) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($productModel, array_merge($options, ['is_new' => $areProductsNew[$i]])),
                StorageEvents::PRE_SAVE
            );

            $this->objectManager->persist($productModel);
        }

        $this->objectManager->flush();

        foreach ($productModels as $productModel) {
            $productModel->cleanup();
        }

        foreach ($productModels as $i => $productModel) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($productModel, array_merge($options, ['is_new' => $areProductsNew[$i]])),
                StorageEvents::POST_SAVE
            );
        }

        $this->eventDispatcher->dispatch(
            new GenericEvent($productModels, $options),
            StorageEvents::POST_SAVE_ALL
        );
    }

    protected function validateProductModel($productModel)
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    ProductModelInterface::class,
                    ClassUtils::getClass($productModel)
                )
            );
        }
    }
}
