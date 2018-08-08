<?php

namespace AkeneoEnterprise\Test\Acceptance\ProductAsset\AssetCategory;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Repository\AssetCategoryRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

class InMemoryAssetCategoryRepository implements IdentifiableObjectRepositoryInterface, SaverInterface, AssetCategoryRepositoryInterface, ItemCategoryRepositoryInterface
{
    /** @var ArrayCollection */
    private $categories;

    public function __construct(array $categories = [])
    {
        $this->categories = new ArrayCollection($categories);
    }

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->categories->get($identifier);
    }

    public function save($category, array $options = [])
    {
        if (!$category instanceof CategoryInterface) {
            throw new \InvalidArgumentException('Only category objects are supported.');
        }

        $this->categories->set($category->getCode(), $category);
    }

    public function getItemCountByGrantedTree(AssetInterface $asset, UserInterface $user)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findRoot()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemCountByTree($item)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsCountInCategory(array $categoryIds = [])
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function findCategoriesItem($item): array
    {
        throw new NotImplementedException();
    }
}
