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
use PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess;

/**
 * Updates a Product Category Access
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProductCategoryAccessUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param IdentifiableObjectRepositoryInterface $productRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $productRepository
    ) {
        $this->groupRepository   = $groupRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *      'category'   => '2013_collection',
     *      'user_group' => 'IT Manager',
     *      'view_items' => true,
     *      'edit_items' => false,
     *      'own_items'  => false,
     * ]
     */
    public function update($categoryAccess, array $data, array $options = [])
    {
        if (!$categoryAccess instanceof ProductCategoryAccess) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess", "%s" provided.',
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
     * @param ProductCategoryAccess $categoryAccess
     * @param string                $field
     * @param mixed                 $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(ProductCategoryAccess $categoryAccess, $field, $data)
    {
        switch ($field) {
            case 'category':
                $category = $this->productRepository->findOneByIdentifier($data);
                if (null === $category) {
                    throw new \InvalidArgumentException(sprintf('Product category with "%s" code does not exist', $data));
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
