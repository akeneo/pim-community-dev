<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Filter\AbstractFilter;
use Pim\Component\Classification\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * A category filter which handles permissions
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class CategoryRightFilter extends AbstractFilter
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * @param SecurityContextInterface $securityContext
     * @param CategoryAccessRepository $categoryAccessRepo
     */
    public function __construct(SecurityContextInterface $securityContext, CategoryAccessRepository $categoryAccessRepo)
    {
        $this->securityContext    = $securityContext;
        $this->categoryAccessRepo = $categoryAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function filterCollection($categories, $type, array $options = [])
    {
        $filteredCategories = [];
        $user = $this->securityContext->getToken()->getUser();
        $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS);

        foreach ($categories as $key => $category) {
            if (in_array($category->getId(), $grantedCategoryIds)) {
                $filteredCategories[$key] = $category;
            }
        }

        return $filteredCategories;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($category, $type, array $options = [])
    {
        if (!$category instanceof CategoryInterface) {
            throw new \LogicException('This filter only handles objects of type "CategoryInterface"');
        }

        return !$this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof CategoryInterface;
    }
}
