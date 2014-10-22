<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Filter\NumberFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CategoryFilterType;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;

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

    /** @var ProductCategoryManager $manager */
    protected $manager;

    /**
     * Constructor
     *
     * @param FormFactoryInterface   $factory
     * @param FilterUtility          $util
     * @param ProductCategoryManager $manager
     */
    public function __construct(FormFactoryInterface $factory, FilterUtility $util, ProductCategoryManager $manager)
    {
        parent::__construct($factory, $util);

        $this->manager = $manager;
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
     * @return boolean has been applied
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
     * @return boolean has been applied
     */
    protected function applyFilterByUnclassified(FilterDatasourceAdapterInterface $ds, $data)
    {
        $categoryRepository = $this->manager->getCategoryRepository();
        $productRepository  = $this->manager->getProductCategoryRepository();
        $qb                 = $ds->getQueryBuilder();

        $tree = $categoryRepository->find($data['treeId']);
        if ($tree) {
            $data['includeSub'] = true;
            $productIds = $this->getProductIdsInCategory($tree, $data);
            $productRepository->applyFilterByIds($qb, $productIds, false);

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
     * @return boolean has been applied
     */
    protected function applyFilterByCategory(FilterDatasourceAdapterInterface $ds, $data)
    {
        $categoryRepository = $this->manager->getCategoryRepository();
        $productRepository  = $this->manager->getProductCategoryRepository();
        $qb                 = $ds->getQueryBuilder();

        $category = $categoryRepository->find($data['categoryId']);

        if (!$category) {
            $category = $categoryRepository->find($data['treeId']);
        }

        if ($category) {
            if ($data['includeSub']) {
                $categoryIds = $categoryRepository->getAllChildrenIds($category);
            } else {
                $categoryIds = array();
            }
            $categoryIds[] = $category->getId();
            $productRepository->applyFilterByCategoryIds($qb, $categoryIds, true);

            return true;
        }

        return false;
    }

    /**
     * Get product ids in category (and children)
     *
     * @param CategoryInterface $category
     * @param array             $data
     *
     * @return integer[]
     */
    protected function getProductIdsInCategory(CategoryInterface $category, $data)
    {
        $productIds = $this->manager->getProductIdsInCategory($category, $data['includeSub']);

        return (empty($productIds)) ? array(0) : $productIds;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return CategoryFilterType::NAME;
    }
}
