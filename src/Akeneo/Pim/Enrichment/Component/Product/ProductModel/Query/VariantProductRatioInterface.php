<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * Query data regarding the variant product completenesses to build the completeness variant product ratio
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VariantProductRatioInterface
{
    /**
     * @param ProductModelInterface $productModel
     *
     * @return CompleteVariantProducts
     */
    public function findComplete(ProductModelInterface $productModel): CompleteVariantProducts;
}
