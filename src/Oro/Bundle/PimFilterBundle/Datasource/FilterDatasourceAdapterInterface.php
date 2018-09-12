<?php

namespace Oro\Bundle\PimFilterBundle\Datasource;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface as OroFilterDatasourceAdapterInterface;

/**
 * Customize the Oro FilterDatasourceAdapterInterface
 *
 * TODO : a lot of cleanup to do in the OroFilterDatasourceAdapterInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterDatasourceAdapterInterface extends OroFilterDatasourceAdapterInterface
{
    /**
     * Get query builder
     *
     * @return mixed
     */
    public function getQueryBuilder();
}
