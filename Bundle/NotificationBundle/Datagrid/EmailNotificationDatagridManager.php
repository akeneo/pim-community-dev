<?php

namespace Oro\Bundle\NotificationBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

class EmailNotificationDatagridManager extends DatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldCollection)
    {

    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        return array();
    }
}
