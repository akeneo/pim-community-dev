<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuids
{
    /**
     * @param array<string, array<mixed>> $searchFilters
     *
     * The format of the search filters is the same as the one used in the external API. For example:
     *  [
     *      'sku' => [
     *          [
     *              'operator' => "IN",
     *              'value': ['SKU1', 'SKU2', 'SKU13],
     *          ],
     *      ],
     *  ]
     */
    public function __construct(private array $searchFilters)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function searchFilters(): array
    {
        return $this->searchFilters;
    }
}
