<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Commands;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetGroupProductIdentifiers;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\ORM\EntityManager;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductsToGroupHandler
{
    private GetGroupProductIdentifiers $getGroupProductIdentifiers;
    private EntityManager $entityManager;
    protected BulkSaverInterface $productSaver;

    public function __construct(GetGroupProductIdentifiers $getGroupProductIdentifiers, EntityManager $entityManager, BulkSaverInterface $productSaver)
    {
        $this->getGroupProductIdentifiers = $getGroupProductIdentifiers;
        $this->entityManager = $entityManager;
        $this->productSaver = $productSaver;
    }

    public function handle(UpdateProductsToGroupCommand $updateProductsToGroupCommand)
    {
        $uptodateProductIds = $updateProductsToGroupCommand->getUptodateProductIds();
        $oldProductIds = $this->getGroupProductIdentifiers->byGroupId($updateProductsToGroupCommand->getGroupId());

        $newProductIds = array_diff($uptodateProductIds, $oldProductIds);
        $removedProductIds = array_diff($oldProductIds, $uptodateProductIds);

        $productsToUpdate = [];
        $group = $this->entityManager->find(Group::class, $updateProductsToGroupCommand->getGroupId());

        foreach ($newProductIds as $newProductId) {
            $dbProduct = $this->entityManager->find(Product::class, $newProductId);
            $dbProduct->addGroup($group);
            $productsToUpdate[] = $dbProduct;
        }
        foreach ($removedProductIds as $removedProductId) {
            $dbProduct = $this->entityManager->find(Product::class, $removedProductId);
            $dbProduct->removeGroup($group);
            $productsToUpdate[] = $dbProduct;
        }

        $this->productSaver->saveAll($productsToUpdate);
    }
}
