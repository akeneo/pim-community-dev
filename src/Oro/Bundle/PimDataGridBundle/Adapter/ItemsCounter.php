<?php

namespace Oro\Bundle\PimDataGridBundle\Adapter;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts;

/**
 * Counts the number of items selected in the grid.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ItemsCounter
{
    /** @var CountImpactedProducts */
    private $countImpactedProducts;

    public function __construct(CountImpactedProducts $countImpactedProducts)
    {
        $this->countImpactedProducts = $countImpactedProducts;
    }

    /**
     * @param string $gridName
     * @param array  $filters
     *
     * @return int
     * @throws \Exception
     */
    public function count(string $gridName, array $filters): int
    {
        if ($gridName === OroToPimGridFilterAdapter::PRODUCT_GRID_NAME) {
            return $this->countImpactedProducts->count($filters);
        }

        if ($gridName === OroToPimGridFilterAdapter::ATTRIBUTE_GRID_NAME) {
            // @TODO RAB-1260: Use a query to count attribute group
            return 0;
        }

        if (!isset($filters[0]['value'])) {
            throw new \Exception('There should one filter containing the items to filter.');
        }

        return count($filters[0]['value']);
    }
}
