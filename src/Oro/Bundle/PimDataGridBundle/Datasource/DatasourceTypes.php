<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

/**
 * Existing datasource types
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatasourceTypes
{
    /**
     * ORM default datasource
     *
     * @staticvar string
     */
    const DATASOURCE_DEFAULT = 'pim_datasource_default';

    /**
     * Datasource used for resources that are stored either via ORM or via MongoDB ODM
     *
     * @staticvar string
     */
    const DATASOURCE_SMART = 'pim_datasource_smart';

    /**
     * Product datasource (either ORM or MongoDB ODM)
     *
     * @staticvar string
     */
    const DATASOURCE_PRODUCT = 'pim_datasource_product';

    /**
     * Oro datasource
     *
     * @staticvar string
     */
    const DATASOURCE_ORO = 'orm';
}
