<?php

namespace Pim\Component\Catalog\ProductAndProductModel\Query;

use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Find data used by the datagrid completeness filter. We need to know if a product model has at least one
 * complete / incomplete variant product for each channel and locale.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CompleteFilterInterface
{
    /**
     * @param ProductModelInterface $productModel
     *
     * @return CompleteFilterData
     */
    public function findCompleteFilterData(ProductModelInterface $productModel): CompleteFilterData;
}
