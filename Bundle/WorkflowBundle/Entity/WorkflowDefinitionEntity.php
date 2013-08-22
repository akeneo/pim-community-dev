<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkflowDefinitionEntity
 *
 * @ORM\Table(name="oro_workflow_definition_entity")
 * @ORM\Entity
 */
class WorkflowDefinitionEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="className", type="string", length=255)
     */
    protected $className;

    /**
     * @var boolean
     *
     * @ORM\Column(name="multiple", type="boolean")
     */
    protected $multiple;

    /**
     * @var WorkflowDefinition
     *
     * @ORM\ManyToOne(targetEntity="WorkflowDefinition", inversedBy="definitionEntities")
     * @ORM\JoinColumn(name="definition_name", referencedColumnName="name", onDelete="CASCADE")
     */
    protected $definition;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set className
     *
     * @param string $className
     * @return WorkflowDefinitionEntity
     */
    public function setClassName($className)
    {
        $this->className = $className;
    
        return $this;
    }

    /**
     * Get className
     *
     * @return string 
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set multiple
     *
     * @param boolean $multiple
     * @return WorkflowDefinitionEntity
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
    
        return $this;
    }

    /**
     * Get multiple
     *
     * @return boolean 
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set workflow definition
     *
     * @param WorkflowDefinition $definition
     * @return WorkflowDefinitionEntity
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
    
        return $this;
    }

    /**
     * Get workflow definition
     *
     * @return WorkflowDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }
}
