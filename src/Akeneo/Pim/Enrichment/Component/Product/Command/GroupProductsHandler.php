<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Command;

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


    public function __construct(FindProductIdentifiersInterface $getGroupProductIdentifiers, GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver, ProductRepositoryInterface $productRepository)
    {
        $this->findGroupProductIdentifiers = $getGroupProductIdentifiers;
        $this->groupRepository = $groupRepository;
        $this->productSaver = $productSaver;
        $this->productRepository = $productRepository;
    }

    public function handle(GroupProductsCommand $updateProductsToGroupCommand)
    {
        $newProductIds = $updateProductsToGroupCommand->productIds();
        $oldProductIds = $this->findGroupProductIdentifiers->fromGroupId($updateProductsToGroupCommand->groupId());

        $addedProductIds = array_diff($newProductIds, $oldProductIds);
        $removedProductIds = array_diff($oldProductIds, $newProductIds);

        $productsToUpdate = [];
        $group = $this->groupRepository->find($updateProductsToGroupCommand->groupId());

        foreach ($addedProductIds as $newProductId) {
            $dbProduct = $this->productRepository->find($newProductId);
            $dbProduct->addGroup($group);
            $productsToUpdate[] = $dbProduct;
        }
        foreach ($removedProductIds as $removedProductId) {
            $dbProduct = $this->productRepository->findOneByIdentifier($removedProductId);
            $dbProduct->removeGroup($group);
            $productsToUpdate[] = $dbProduct;
        }

        $this->productSaver->saveAll($productsToUpdate);
    }
}
