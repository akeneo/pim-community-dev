<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\Stub;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

/**
 * Stub entity class
 */
class StubDatagridManager extends DatagridManager
{
    private $fields;

    private $properties;

    private $rowActions;

    public function __construct(array $fields, array $properties, array $rowActions)
    {
        $this->fields = $fields;
        $this->properties = $properties;
        $this->rowActions = $rowActions;
    }

    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        foreach ($this->fields as $field) {
            $fieldsCollection->add($field);
        }
    }

    protected function getProperties()
    {
        return $this->properties;
    }

    protected function getRowActions()
    {
        return $this->rowActions;
    }
}
