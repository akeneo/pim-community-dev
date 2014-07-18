<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datasource;

use Pim\Bundle\DataGridBundle\Datasource\DatasourceTypes as BaseDatasourceTypes;

/**
 * Existing datasource types
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DatasourceTypes extends BaseDatasourceTypes
{
    /**
     * Product history datasource (either ORM or MongoDB ODM)
     *
     * @staticvar string
     */
    const DATASOURCE_PRODUCT_HISTORY = 'pimee_datasource_product_history';
}
