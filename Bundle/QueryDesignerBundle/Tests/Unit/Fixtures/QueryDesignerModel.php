<?php

namespace Oro\Bundle\QueryDesignerBundle\Tests\Unit\Fixtures;

use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;

class QueryDesignerModel extends AbstractQueryDesigner
{
    /** @var string */
    private $entity;

    /** @var string */
    private $definition;

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function setDefinition($definition)
    {
        $this->definition = $definition;
    }
}
