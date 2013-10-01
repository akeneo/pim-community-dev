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
     * @var WorkflowDefinition
     *
     * @ORM\ManyToOne(targetEntity="WorkflowDefinition", inversedBy="workflowDefinitionEntities")
     * @ORM\JoinColumn(name="workflow_definition_name", referencedColumnName="name", onDelete="CASCADE")
     */
    protected $workflowDefinition;

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
     * Set workflow definition
     *
     * @param WorkflowDefinition $definition
     * @return WorkflowDefinitionEntity
     */
    public function setWorkflowDefinition($definition)
    {
        $this->workflowDefinition = $definition;
    
        return $this;
    }

    /**
     * Get workflow definition
     *
     * @return WorkflowDefinition
     */
    public function getWorkflowDefinition()
    {
        return $this->workflowDefinition;
    }
}
