<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class RoleUsersGridListener extends AbstractUsersGridListener
{
    const GRID_PARAM_ROLE_ID     = 'role_id';

    /**
     * {@inheritdoc}
     */
    public function getParamName()
    {
        return self::GRID_PARAM_ROLE_ID;
    }
}
