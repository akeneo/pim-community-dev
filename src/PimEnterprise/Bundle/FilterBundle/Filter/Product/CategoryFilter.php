<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\Product\CategoryFilter as BaseCategoryFilter;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Override category filter to apply permissions on categories
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryFilter extends BaseCategoryFilter
{
    /**
     * Override to apply category permissions
     *
     * {@inheritdoc}
     */
    protected function applyFilterByAll(FilterDatasourceAdapterInterface $ds, $data)
    {
        $qb = $ds->getQueryBuilder();
        $this->manager->addFilterByAll($qb);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterByUnclassified(FilterDatasourceAdapterInterface $ds, $data)
    {
        $this->applyFilterByAll($ds, $data);

        return parent::applyFilterByUnclassified($ds, $data);
    }

    /**
     * Override to apply category permissions (not for unclassified)
     *
     * {@inheritdoc}
     */
    protected function getProductIdsInCategory(CategoryInterface $category, $data)
    {
        if ($data['categoryId'] === self::UNCLASSIFIED_CATEGORY) {
            $productIds = $this->manager->getProductIdsInCategory($category, $data['includeSub']);
        } else {
            $productIds = $this->manager->getProductIdsInGrantedCategory($category, $data['includeSub']);
        }

        return (empty($productIds)) ? array(0) : $productIds;
    }
}
