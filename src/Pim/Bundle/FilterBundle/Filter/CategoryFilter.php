<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Filter\NumberFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CategoryFilterType;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

/**
 * Category filter
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilter extends NumberFilter
{
    /**
     * @staticvar integer
     */
    const UNCLASSIFIED_CATEGORY = -1;

    /**
     * @var ProductManager $productManager
     */
    protected $productManager;

    /**
     * @var CategoryManager $categoryManager
     */
    protected $categoryManager;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     * @param ProductManager       $productManager
     * @param CategoryManager      $categoryManager
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        ProductManager $productManager,
        CategoryManager $categoryManager
    ) {
        parent::__construct($factory, $util);

        $this->productManager = $productManager;
        $this->categoryManager = $categoryManager;
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

        $includeSub = $data['includeSub'];
        $treeId     = $data['treeId'];
        $categoryId = $data['categoryId'];

        $repository = $this->categoryManager->getEntityRepository();

        $qb = $ds->getQueryBuilder();
        $rootAlias = $qb->getRootAlias();

        if ($categoryId === self::UNCLASSIFIED_CATEGORY) {
            $tree = $repository->find($treeId);
            if ($tree) {
                $productIds = $this->productManager->getProductIdsInCategory($tree, true);
                $productIds = (empty($productIds)) ? array(0) : $productIds;

                $expression = $qb->expr()->notIn($rootAlias .'.id', $productIds);
                $qb->andWhere($expression);

                return true;
            }
        } else {
            $category = $repository->find($categoryId);
            if (!$category) {
                $category = $repository->find($treeId);
            }
            if ($category) {
                $productIds = $this->productManager->getProductIdsInCategory($category, $includeSub);
                $productIds = (empty($productIds)) ? array(0) : $productIds;

                $expression = $qb->expr()->in($rootAlias .'.id', $productIds);
                $qb->andWhere($expression);

                return true;
            }
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

        $data['includeSub'] = isset($data['type'])                ? (bool) $data['type']               : null;
        $data['treeId']     = isset($data['value']['treeId'])     ? (int) $data['value']['treeId']     : null;
        $data['categoryId'] = isset($data['value']['categoryId']) ? (int) $data['value']['categoryId'] : null;
        unset($data['type']);
        unset($data['value']);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return CategoryFilterType::NAME;
    }
}
