<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

class EntityDatagrid extends DatagridManager
{
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {

    }
}
