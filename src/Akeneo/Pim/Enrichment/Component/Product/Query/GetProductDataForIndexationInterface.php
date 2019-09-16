<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductDataForIndexationInterface
{
    /**
     * Returns an associative array for product indexing.
     *
     * @param string $productIdentifier
     * @return array
     */
    public function fromProductIdentifier(string $productIdentifier): array;

    /**
     * Returns list of associative arrays for product indexing.
     *
     * @param array $productIdentifiers
     * @return array
     *      [
     *          'product_1' => ['key_1_to_index' => 'value_1_to_index'],
     *          'product_2' => ['key_1_to_index' => 'value_1_to_index']
     *      ]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array;
}
