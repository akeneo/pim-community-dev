<?php

namespace Oro\Bundle\QueryDesignerBundle\Model;

abstract class AbstractQueryDesigner
{
    /**
     * Get the full name of an entity on which this report is based
     *
     * @return string
     */
    abstract public function getEntity();

    /**
     * Set the full name of an entity on which this report is based
     *
     * @param string $entity
     */
    abstract public function setEntity($entity);

    /**
     * Get this report definition in YAML format
     *
     * @return string
     */
    abstract public function getDefinition();

    /**
     * Set this report definition in YAML format
     *
     * @param string $definition
     */
    abstract public function setDefinition($definition);
}
