<?php

namespace Pim\Bundle\DataGridBundle\Extension\Filter;

use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension as OroOrmFilterExtension;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

/**
 * Orm filter extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmFilterExtension extends OroOrmFilterExtension
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $filters = $config->offsetGetByPath(Configuration::COLUMNS_PATH, []);

        if (!$filters) {
            return false;
        }

        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) === OrmDatasource::TYPE;
    }
}
