<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductIdentifiersInterface;
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
    private FindProductIdentifiersInterface $findGroupProductIdentifiers;
    private GroupRepositoryInterface $groupRepository;
    private BulkSaverInterface $productSaver;
    private ProductRepositoryInterface $productRepository;
    private int $batchSize;


    public function __construct(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository, int $batchSize)
    {
        $this->findGroupProductIdentifiers = $getGroupProductIdentifiers;
        $this->groupRepository = $groupRepository;
        $this->productSaver = $productSaver;
        $this->productRepository = $productRepository;
        $this->batchSize = $batchSize;
    }

    public function handle(GroupProductsCommand $updateProductsToGroupCommand)
    {
        $newProductIds = $updateProductsToGroupCommand->productIds();
        $oldProductIds = $this->findGroupProductIdentifiers->fromGroupId($updateProductsToGroupCommand->groupId());

        $addedProductIds = array_diff($newProductIds, $oldProductIds);
        $removedProductIds = array_diff($oldProductIds, $newProductIds);

        $group = $this->groupRepository->find($updateProductsToGroupCommand->groupId());

        $this->batchProductExecution(
            $group,
            $addedProductIds,
            function (GroupInterface $group, Product $product) {
                $product->addGroup($group);
            }
        );

        $this->batchProductExecution(
            $group,
            $removedProductIds,
            function (GroupInterface $group, Product $product) {
                $product->removeGroup($group);
            }
        );
    }

    protected function batchProductExecution($group, array $productIds, \Closure $addGroup)
    {
        $batchedProductIdsList = \array_chunk($productIds, $this->batchSize);
        foreach ($batchedProductIdsList as $productIdList) {
            $batchedProducts = [];
            foreach ($productIdList as $productId) {
                $product = $this->productRepository->findOneByIdentifier($productId);
                $addGroup($group, $product);
                $batchedProducts[]=$product;
            }
            $this->productSaver->saveAll($batchedProducts);
        }
    }
}
