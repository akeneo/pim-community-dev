<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use PimEnterprise\Bundle\FilterBundle\Filter\CategoryFilter;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Datagrid view access manager
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class DatagridViewAccessManager
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var AttributeGroupAccessManager */
    protected $attGrpAccessManager;

    /** @var CategoryAccessManager */
    protected $catAccessManager;

    /**
     * @param AttributeRepository         $attributeRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param AttributeGroupAccessManager $attGrpAccessManager
     * @param CategoryAccessManager       $catAccessManager
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        CategoryRepositoryInterface $categoryRepository,
        AttributeGroupAccessManager $attGrpAccessManager,
        CategoryAccessManager $catAccessManager
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
        $this->attGrpAccessManager = $attGrpAccessManager;
        $this->catAccessManager = $catAccessManager;
    }

    /**
     * Chek is a user is granted on a datagrid view for the given attribute.
     *
     * @param UserInterface $user
     * @param DatagridView  $view
     * @param string        $attribute
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function isUserGranted(UserInterface $user, DatagridView $view, $attribute)
    {
        if (Attributes::VIEW !== $attribute) {
            throw new \LogicException(sprintf('Attribute "%" is not supported.', $attribute));
        }

        foreach ($view->getColumns() as $column) {
            if (false === $this->isAttributeGranted($user, $column)) {
                return false;
            }
        }

        foreach ($this->getViewFiltersAsArray($view) as $filter) {
            if (false === $this->isAttributeGranted($user, $filter)) {
                return false;
            }
        }

        if (null !== $categoryId = $this->getCategoryIdFromViewFilters($view)) {
            return $this->isCategoryGranted($user, $categoryId);
        }

        return true;
    }

    /**
     * Check if an attribute is granted for the current user (ie: if the attribute group is granted for view)
     *
     * @param UserInterface $user
     * @param string        $code
     *
     * @return bool
     */
    protected function isAttributeGranted(UserInterface $user, $code)
    {
        /** @var \Pim\Bundle\CatalogBundle\Entity\Attribute $attribute */
        if (null === $attribute = $this->attributeRepository->findOneBy(['code' => $code])) {
            return true;
        }

        return $this->attGrpAccessManager->isUserGranted(
            $user,
            $attribute->getGroup(),
            Attributes::VIEW_ATTRIBUTES
        );
    }

    /**
     * Check if a category is granted for the current user (ie: if the category is granted for view)
     *
     * @param UserInterface $user
     * @param int           $categoryId
     *
     * @return bool
     */
    protected function isCategoryGranted(UserInterface $user, $categoryId)
    {
        if (CategoryFilter::ALL_CATEGORY == $categoryId || CategoryFilter::UNCLASSIFIED_CATEGORY == $categoryId) {
            return true;
        }

        /** @var \Pim\Bundle\CatalogBundle\Model\CategoryInterface $category */
        if (null === $category = $this->categoryRepository->find($categoryId)) {
            return false;
        }

        return $this->catAccessManager->isUserGranted($user, $category, Attributes::VIEW_ITEMS);
    }

    /**
     * TODO:  change the way view filters are stored in the DB and remove this ugly hack...
     *
     * @param DatagridView $view
     *
     * @return int|null
     */
    protected function getCategoryIdFromViewFilters(DatagridView $view)
    {
        $matches = [];
        preg_match('/f\[category\]\[value\]\[categoryId\]=((?:-)?\d+)/', urldecode($view->getFilters()), $matches);

        // no filter on categories
        if (empty($matches[1])) {
            return null;
        }

        if (is_string($matches[1])) {
            return $matches[1];
        }

        return $matches[1][0];
    }

    /**
     * TODO:  change the way view filters are stored in the DB and remove this ugly hack...
     *
     * @param DatagridView $view
     *
     * @return array
     */
    protected function getViewFiltersAsArray(DatagridView $view)
    {
        $matches = [];
        preg_match_all('/f\[(.*?)\].*?=([\w\d]|\-\d)/', urldecode($view->getFilters()), $matches);

        $filters = array_unique($matches[1]);

        return array_map(
            function ($filter) {
                return str_replace('__', '', $filter);
            },
            $filters
        );
    }
}
