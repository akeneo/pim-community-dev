<?php

namespace AkeneoEnterprise\Test\Acceptance\ProductAsset\AssetCategory;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\User\Model\UserInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetCategoryRepositoryInterface;

class InMemoryAssetCategoryRepository implements IdentifiableObjectRepositoryInterface, SaverInterface, AssetCategoryRepositoryInterface
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
}
