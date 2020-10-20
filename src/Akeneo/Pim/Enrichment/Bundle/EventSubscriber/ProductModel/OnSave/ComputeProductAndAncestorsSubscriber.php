<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetDescendantVariantProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Orchestrator for below jobs on update:
 *  - Computes and saves the completenesses for the variant products of the given product models.
 *  - Indexes these variant products
 *  - Indexes the product models impacted by the new completeness (= ancestors and descendants of the given product models)
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductAndAncestorsSubscriber implements EventSubscriberInterface
{
    /** @var ComputeAndPersistProductCompletenesses */
    private $computeAndPersistProductCompletenesses;

    /** @var ProductModelDescendantsAndAncestorsIndexer */
    private $productModelDescendantsAndAncestorsIndexer;

    /** @var GetDescendantVariantProductIdentifiers */
    private $getDescendantVariantProductIdentifiers;

    public function __construct(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $this->computeAndPersistProductCompletenesses = $computeAndPersistProductCompletenesses;
        $this->productModelDescendantsAndAncestorsIndexer = $productModelDescendantsAndAncestorsIndexer;
        $this->getDescendantVariantProductIdentifiers = $getDescendantVariantProductIdentifiers;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'onProductModelSave',
            StorageEvents::POST_SAVE_ALL => 'onProductModelSaveAll',
        ];
    }

    public function onProductModelSave(Event $event): void
    {
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->computeAndIndexFromProductModelCodes([$productModel->getCode()]);
    }

    public function onProductModelSaveAll(Event $event): void
    {
        $productModels = $event->getSubject();
        if (!is_array($productModels)) {
            return;
        }

        if (!current($productModels) instanceof ProductModelInterface) {
            return;
        }

        $this->computeAndIndexFromProductModelCodes(array_map(
            function (ProductModelInterface $productModel) {
                return $productModel->getCode();
            },
            $productModels
        ));
    }

    private function computeAndIndexFromProductModelCodes(array $productModelCodes): void
    {
        if (empty($productModelCodes)) {
            return;
        }

        $variantProductIdentifiers = $this->getDescendantVariantProductIdentifiers->fromProductModelCodes(
            $productModelCodes
        );
        if (!empty($variantProductIdentifiers)) {
            $this->computeAndPersistProductCompletenesses->fromProductIdentifiers($variantProductIdentifiers);
        }

        $this->productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes($productModelCodes);
    }
}
