<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Query;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\ReadModel\CompleteVariantProducts;

/**
 * Query data regarding the variant product completenesses to build the completeness variant product ratio
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindCompleteVariantProductsInterface
{
    /**
     * @param ProductModelInterface $productModel
     *
     * @return CompleteVariantProducts
     */
    public function __invoke(ProductModelInterface $productModel): CompleteVariantProducts;
}
