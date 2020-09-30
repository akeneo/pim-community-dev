<?php

namespace Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;

class ProductTypologyFilter extends ChoiceFilter
{
    private const PRODUCT_TYPOLOGY_SIMPLE = 'simple';
    private const PRODUCT_TYPOLOGY_VARIANT = 'variant';

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        switch (current($data['value'])) {
            case self::PRODUCT_TYPOLOGY_SIMPLE:
                $this->util->applyFilter($ds, 'family_variant', Operators::IS_EMPTY, null);
                break;
            case self::PRODUCT_TYPOLOGY_VARIANT:
                $this->util->applyFilter($ds, 'family_variant', Operators::IS_NOT_EMPTY, null);
                break;
            default: return false;
        }

        return true;
    }
}
