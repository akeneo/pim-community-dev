<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datasource;

use Pim\Bundle\DataGridBundle\Datasource\DatasourceTypes as BaseDatasourceTypes;

/**
 * Existing datasource types
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class DatasourceTypes extends BaseDatasourceTypes
{
    /**
     * Product history datasource (either ORM or MongoDB ODM)
     *
     * @staticvar string
     */
    const DATASOURCE_PRODUCT_HISTORY = 'pimee_datasource_product_history';

    /**
     * Published product datasource (either ORM or MongoDB ODM)
     *
     * @staticvar string
     */
    const DATASOURCE_PUBLISHED_PRODUCT = 'pimee_datasource_published_product';
}
