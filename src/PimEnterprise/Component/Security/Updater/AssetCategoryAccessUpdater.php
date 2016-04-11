<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess;

/**
 * Updates an Asset Category Access
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AssetCategoryAccessUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->groupRepository    = $groupRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *      'category'   => 'videos',
     *      'user_group'  => 'IT Manager',
     *      'view_items' => true,
     *      'edit_items' => false,
     *      'own_items'  => false,
     * ]
     */
    public function update($categoryAccess, array $data, array $options = [])
    {
        if (!$categoryAccess instanceof AssetCategoryAccess) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess", "%s" provided.',
                    ClassUtils::getClass($categoryAccess)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($categoryAccess, $field, $value);
        }

        return $this;
    }

    /**
     * @param AssetCategoryAccess $categoryAccess
     * @param string              $field
     * @param mixed               $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(AssetCategoryAccess $categoryAccess, $field, $data)
    {
        switch ($field) {
            case 'category':
                $category = $this->categoryRepository->findOneByIdentifier($data);
                if (null === $category) {
                    throw new \InvalidArgumentException(sprintf('Asset category with "%s" code does not exist', $data));
                }
                $categoryAccess->setCategory($category);
                break;
            case 'user_group':
                $group = $this->groupRepository->findOneByIdentifier($data);
                if (null === $group) {
                    throw new \InvalidArgumentException(sprintf('Group with "%s" code does not exist', $data));
                }
                $categoryAccess->setUserGroup($group);
                break;
            case 'view_items':
                $categoryAccess->setViewItems($data);
                break;
            case 'edit_items':
                $categoryAccess->setEditItems($data);
                break;
            case 'own_items':
                $categoryAccess->setOwnItems($data);
                break;
        }
    }
}
