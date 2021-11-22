<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Commands;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductIdentifiersInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProductsHandler
{
    private FindProductIdentifiersInterface $getGroupProductIdentifiers;
    private GroupRepositoryInterface $groupRepository;
    private BulkSaverInterface $productSaver;
    private ProductRepositoryInterface $productRepository;


    public function __construct(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository)
    {
        $this->getGroupProductIdentifiers = $getGroupProductIdentifiers;
        $this->groupRepository = $groupRepository;
        $this->productSaver = $productSaver;
        $this->productRepository = $productRepository;
    }

    public function handle(GroupProductsCommand $updateProductsToGroupCommand)
    {
        $currentProductIds = $updateProductsToGroupCommand->productIds();
        $oldProductIds = $this->getGroupProductIdentifiers->byGroupId($updateProductsToGroupCommand->getGroupId());

        $newProductIds = array_diff($currentProductIds, $oldProductIds);
        $removedProductIds = array_diff($oldProductIds, $currentProductIds);

        $productsToUpdate = [];
        $group = $this->groupRepository->find($updateProductsToGroupCommand->getGroupId());

        foreach ($newProductIds as $newProductId) {
            $dbProduct = $this->productRepository->find($newProductId);
            $dbProduct->addGroup($group);
            $productsToUpdate[] = $dbProduct;
        }
        foreach ($removedProductIds as $removedProductId) {
            $dbProduct = $this->productRepository->find($removedProductId);
            $dbProduct->removeGroup($group);
            $productsToUpdate[] = $dbProduct;
        }

        $this->productSaver->saveAll($productsToUpdate);
    }
}
