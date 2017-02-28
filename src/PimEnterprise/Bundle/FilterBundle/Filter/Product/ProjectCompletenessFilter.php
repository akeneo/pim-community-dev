<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProjectCompletenessFilter extends OroChoiceFilter
{
    /**
     * Filter by permissions on category ids (category with owner permissions or not classified at all)
     *
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        // Since needed tables are not mapped, we do the logic elsewhere.
    }

    /**
     * @param array $data
     *
     * @return array|false
     */
    protected function parseData($data)
    {
        return $data;
    }
}
