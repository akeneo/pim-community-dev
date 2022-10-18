<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Warning: This handler implementation shows performance limitations due to reuse of \Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface::saveAll
 * All impacted products are loaded...
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProductsHandler
{
    public function __construct(
        private FindProductUuidsInGroup $findUuids,
        private GroupRepositoryInterface $groupRepository,
        private BulkSaverInterface $productSaver,
        private ProductRepositoryInterface $productRepository,
        private int $batchSize
    ) {
    }

    public function handle(GroupProductsCommand $updateProductsToGroupCommand): void
    {
        $newProductUuids = $updateProductsToGroupCommand->productUuids();
        $formerProductUuids = $this->findUuids->forGroupId($updateProductsToGroupCommand->groupId());

        $addedProductUuids = \array_diff($newProductUuids, $formerProductUuids);
        $removedProductUuids = \array_diff($formerProductUuids, $newProductUuids);

        $group = $this->groupRepository->find($updateProductsToGroupCommand->groupId());

        $this->batchProductExecution(
            $group,
            $removedProductUuids,
            function (GroupInterface $group, ProductInterface $product) {
                $product->removeGroup($group);
            }
        );

        $this->batchProductExecution(
            $group,
            $addedProductUuids,
            function (GroupInterface $group, ProductInterface $product) {
                $product->addGroup($group);
            }
        );
    }

    protected function batchProductExecution($group, array $productUuids, \Closure $closure): void
    {
        $batchedProductUuidsList = \array_chunk($productUuids, $this->batchSize);
        foreach ($batchedProductUuidsList as $productUuidList) {
            $batchedProducts = [];
            $products = $this->productRepository->getItemsFromUuids($productUuidList);
            foreach ($products as $product) {
                $closure($group, $product);
                $batchedProducts[] = $product;
            }
            $this->productSaver->saveAll($batchedProducts);
        }
    }
}
