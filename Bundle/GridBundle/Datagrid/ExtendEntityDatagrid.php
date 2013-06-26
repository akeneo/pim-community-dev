<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ExtendEntityDatagrid extends DatagridManager
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
