<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Adapter;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeSearchableRepository;

/**
 * Counts the number of items selected in the grid.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ItemsCounter
{
    public function __construct(
        private readonly CountImpactedProducts $countImpactedProducts,
        private readonly AttributeSearchableRepository $attributeRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function count(string $gridName, array $filters): int
    {
        if ($gridName === OroToPimGridFilterAdapter::PRODUCT_GRID_NAME) {
            return $this->countImpactedProducts->count($filters);
        }

        if ($gridName === OroToPimGridFilterAdapter::ATTRIBUTE_GRID_NAME) {
            return $this->attributeRepository->count($filters['search'], $filters['options']);
        }

        if (!isset($filters[0]['value'])) {
            throw new \Exception('There should one filter containing the items to filter.');
        }

        return count($filters[0]['value']);
    }
}
