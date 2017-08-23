<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\Console\CommandLauncher;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Index product models descendants in the search engine.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductModelDescendantsSubscriber implements EventSubscriberInterface
{
    /** @var CommandLauncher */
    private $commandLauncher;

    /** @var RemoverInterface */
    private $productModelDescendantsRemover;

    /**
     * @param RemoverInterface $productModelDescendantsRemover
     * @param CommandLauncher  $commandLauncher
     */
    public function __construct(
        RemoverInterface $productModelDescendantsRemover,
        CommandLauncher $commandLauncher
    ) {
        $this->productModelDescendantsRemover = $productModelDescendantsRemover;
        $this->commandLauncher = $commandLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE     => 'indexProductModelDescendants',
            StorageEvents::POST_SAVE_ALL => 'bulkIndexProductModelsDescendants',
            StorageEvents::POST_REMOVE   => 'deleteProductModelDescendants',
        ];
    }

    /**
     * Index one product model descendants.
     *
     * @param GenericEvent $event
     */
    public function indexProductModelDescendants(GenericEvent $event): void
    {
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->indexProductModelsDescendantsInBackground([$productModel->getCode()]);
    }

    /**
     * Index several product models descendants.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProductModelsDescendants(GenericEvent $event): void
    {
        $productModels = $event->getSubject();
        if (!is_array($productModels)) {
            return;
        }

        if (!current($productModels) instanceof ProductModelInterface) {
            return;
        }

        $productModelsCodes = array_map(function ($productModel) {
            return $productModel->getCode();
        }, $productModels);

        $this->indexProductModelsDescendantsInBackground($productModelsCodes);
    }

    /**
     * Remove one product model descendants from the index.
     *
     * @param RemoveEvent $event
     */
    public function deleteProductModelDescendants(RemoveEvent $event): void
    {
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        $this->productModelDescendantsRemover->remove($productModel);
    }

    /**
     * Run the indexing of the product model descendants in a background job by calling a command.
     *
     * @param array $productModelCodes
     */
    private function indexProductModelsDescendantsInBackground(array $productModelCodes): void
    {
        $this->commandLauncher->executeBackground(
            'pim:product-model:index ' . implode(' ', $productModelCodes)
        );
    }
}
