<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

/**
 * Overriding of boolean filter to filter by the product completeness
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends BooleanFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $qb    = $ds->getQueryBuilder();
        $field = 'completeness.ratio';

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $expression = $qb->expr()->eq($field, '100');
                break;
            case BooleanFilterType::TYPE_NO:
            default:
                $expression = $qb->expr()->orX(
                    $qb->expr()->neq($field, '100'),
                    $qb->expr()->isNull($field)
                );
                break;
        }

        $this->applyFilterToClause($ds, $expression);

        return true;
    }
}
