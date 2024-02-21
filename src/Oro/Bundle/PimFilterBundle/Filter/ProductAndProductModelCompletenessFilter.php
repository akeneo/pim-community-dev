<?php
declare(strict_types=1);

namespace Oro\Bundle\PimFilterBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

/**
 * Overriding of boolean filter to filter by the product and product model completeness
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelCompletenessFilter extends BooleanFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data): bool
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $this->util->applyFilter($ds, 'completeness', Operators::AT_LEAST_COMPLETE, null);
                break;
            case BooleanFilterType::TYPE_NO:
                $this->util->applyFilter($ds, 'completeness', Operators::AT_LEAST_INCOMPLETE, null);
                break;
        }

        return true;
    }
}
