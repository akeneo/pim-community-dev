<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Query;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\ReadModel\VariantProductCompleteness;

/**
 * Query data regarding the variant product completenesses to build the ratio on the PMEF.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindVariantProductCompletenessInterface
{
    /**
     * @param ProductModelInterface $productModel
     *
     * @return VariantProductCompleteness
     */
    public function __invoke(ProductModelInterface $productModel): VariantProductCompleteness;
}
