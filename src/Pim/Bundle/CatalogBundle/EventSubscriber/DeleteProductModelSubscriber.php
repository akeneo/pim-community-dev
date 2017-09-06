<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\Console\CommandLauncher;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Whenever a product model is removed, we also remove it from the index.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteProductModelSubscriber implements EventSubscriberInterface
{
    /** @var RemoverInterface */
    private $productModelIndexer;

    /**
     * @param RemoverInterface $productModelDescendantsRemover
     * @param CommandLauncher  $commandLauncher
     */
    public function __construct(RemoverInterface $productModelIndexer)
    {
        $this->productModelIndexer = $productModelIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'deleteProductModel',
        ];
    }

    /**
     * Remove one product model from the index.
     *
     * @param RemoveEvent $event
     */
    public function deleteProductModel(RemoveEvent $event): void
    {
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        $this->productModelIndexer->remove($productModel);
    }
}
