<?php

namespace Pim\Bundle\FilterBundle\Datasource;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface as OroFilterDatasourceAdapterInterface;

/**
 * Customize the Oro FilterDatasourceAdapterInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterDatasourceAdapterInterface extends OroFilterDatasourceAdapterInterface
{
    /**
     * Return value format depending on comparison type
     *
     * @param string $comparisonType
     *
     * @return string
     */
    public function getFormatByComparisonType($comparisonType);
}
