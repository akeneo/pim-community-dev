<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class EntityDatagrid extends DatagridManager
{
    protected $extendManager;

    public function __construct(ExtendManager $extendManager)
    {
        $this->extendManager = $extendManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {

    }
}
