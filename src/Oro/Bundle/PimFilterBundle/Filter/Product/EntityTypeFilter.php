<?php

namespace Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;

/**
 * This filter is design to display the product grid by grouping or not grouping variant products.
 * We only need to filter the entity type with "Product" class for now, that's why there is no other filter options.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTypeFilter extends ChoiceFilter
{
    const ENTITY_TYPE_PRODUCT = 'product';

    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        switch (current($data['value'])) {
            case self::ENTITY_TYPE_PRODUCT:
                $this->util->applyFilter($ds, 'entity_type', Operators::EQUALS, ProductInterface::class);
                break;
            default: return false;
        }

        return true;
    }
}
