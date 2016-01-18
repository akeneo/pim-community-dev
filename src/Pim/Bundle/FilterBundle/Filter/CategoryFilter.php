<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Filter\NumberFilter;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CategoryFilterType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Category filter
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilter extends NumberFilter
{
    /** @staticvar integer */
    const UNKNOWN_TREE = 0;

    /** @staticvar integer */
    const DEFAULT_TYPE = 1;

    /** @staticvar integer */
    const UNCLASSIFIED_CATEGORY = -1;

    /** @staticvar integer */
    const ALL_CATEGORY = -2;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepo;

    /**
     * @param FormFactoryInterface        $factory
     * @param FilterUtility               $util
     * @param CategoryRepositoryInterface $categoryRepo
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        CategoryRepositoryInterface $categoryRepo
    ) {
        parent::__construct($factory, $util);

        $this->categoryRepo = $categoryRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        if ($data['categoryId'] === self::ALL_CATEGORY) {
            return $this->applyFilterByAll($ds, $data);
        } elseif ($data['categoryId'] === self::UNCLASSIFIED_CATEGORY) {
            return $this->applyFilterByUnclassified($ds, $data);
        } else {
            return $this->applyFilterByCategory($ds, $data);
        }

        return false;
    }

    /**
     * @param mixed $data
     *
     * @return array|bool
     */
    public function parseData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !is_array($data['value'])) {
            return false;
        }

        return [
            'includeSub' => isset($data['type'])                ? (bool) $data['type']               : true,
            'treeId'     => isset($data['value']['treeId'])     ? (int) $data['value']['treeId']     : null,
            'categoryId' => isset($data['value']['categoryId']) ? (int) $data['value']['categoryId'] : null
        ];
    }

    /**
     * Add filter to display all products
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param array                            $data
     *
     * @return bool has been applied
     */
    protected function applyFilterByAll(FilterDatasourceAdapterInterface $ds, $data)
    {
        return true;
    }

    /**
     * Add filter to display unclassified products
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param array                            $data
     *
     * @return bool has been applied
     */
    protected function applyFilterByUnclassified(FilterDatasourceAdapterInterface $ds, $data)
    {
        $tree = $this->categoryRepo->find($data['treeId']);
        if ($tree) {
            $categoryIds = $this->getAllChildrenIds($tree);
            $this->util->applyFilter($ds, 'categories.id', 'NOT IN', $categoryIds);

            return true;
        }

        return false;
    }

    /**
     * Add filter to display categorized products
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param array                            $data
     *
     * @return bool has been applied
     */
    protected function applyFilterByCategory(FilterDatasourceAdapterInterface $ds, $data)
    {
        $category = $this->categoryRepo->find($data['categoryId']);

        if (!$category) {
            $category = $this->categoryRepo->find($data['treeId']);
        }

        if ($category) {
            if ($data['includeSub']) {
                $categoryIds = $this->getAllChildrenIds($category);
            } else {
                $categoryIds = array();
            }
            $categoryIds[] = $category->getId();
            $this->util->applyFilter($ds, 'categories.id', 'IN', $categoryIds);

            return true;
        }

        return false;
    }

    /**
     * Get children category ids
     *
     * @param CategoryInterface $category
     *
     * @return integer[]
     */
    protected function getAllChildrenIds(CategoryInterface $category)
    {
        $categoryIds = $this->categoryRepo->getAllChildrenIds($category);

        return $categoryIds;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return CategoryFilterType::NAME;
    }
}
