<?php

namespace Oro\Bundle\GridBundle\Datagrid;

class DatagridDefinition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array|mixed
     */
    protected $definition = array();

    /**
     * @param string $name
     * @param mixed $datagridDefinitionArray
     */
    public function __construct($name, $datagridDefinitionArray)
    {
        $this->name = $name;
        $this->definition = $datagridDefinitionArray;
    }

    /**
     * @param array|mixed $definition
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return array|mixed
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
