<?php

namespace Oro\Bundle\UserBundle\EventListener;

class GroupUsersGridListener extends AbstractUsersGridListener
{
    const GRID_PARAM_GROUP_ID     = 'group_id';

    /**
     * {@inheritdoc}
     */
    public function getParamName()
    {
        return self::GRID_PARAM_GROUP_ID;
    }
}
