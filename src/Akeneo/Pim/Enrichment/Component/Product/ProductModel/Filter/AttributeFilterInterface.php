<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeFilterInterface
{
    /**
     * Filters imported data from products, variant products and product models.
     * Only data corresponding to attributes contained by family and/or family variant are kept.
     *
     * @param array $item
     *
     * @return array
     */
    public function filter(array $item): array;
}
