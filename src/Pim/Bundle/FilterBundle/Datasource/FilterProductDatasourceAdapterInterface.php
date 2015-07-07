<?php

namespace Pim\Bundle\FilterBundle\Datasource;

use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;

/**
 * Adapter to apply filters on product datasource
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterProductDatasourceAdapterInterface extends FilterDatasourceAdapterInterface
{
    /**
     * Gets a product query builder
     *
     * @return ProductQueryBuilderInterface
     */
    public function getProductQueryBuilder();
}
