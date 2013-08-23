<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Definition of Workflow
 *
 * @ORM\Table(name="oro_workflow_definition")
 * @ORM\Entity(repositoryClass="Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowDefinitionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class WorkflowDefinition
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $label;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var string
     *
     * @ORM\Column(name="start_step", type="string", length=255, nullable=true)
     */
    protected $startStep;

    /**
     * @var array
     *
     * @ORM\Column(name="configuration", type="array")
     */
    protected $configuration;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="WorkflowDefinitionEntity",
     *      mappedBy="workflowDefinition",
     *      orphanRemoval=true,
     *      cascade={"all"}
     * )
     */
    protected $workflowDefinitionEntities;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->configuration = array();
        $this->workflowDefinitionEntities = new ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return WorkflowDefinition
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return WorkflowDefinition
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return WorkflowDefinition
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool)$enabled;

        return $this;
    }

    /**
     * Is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set configuration
     *
     * @param array $configuration
     * @return WorkflowDefinition
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string $startStep
     * @return WorkflowDefinition
     */
    public function setStartStep($startStep)
    {
        $this->startStep = $startStep;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartStep()
    {
        return $this->startStep;
    }

    /**
     * @param Collection|WorkflowDefinitionEntity[] $definitionEntities
     * @return WorkflowDefinition
     */
    public function setWorkflowDefinitionEntities($definitionEntities)
    {
        /** @var WorkflowDefinitionEntity $entity */
        $newEntities = array();
        foreach ($definitionEntities as $entity) {
            $newEntities[$entity->getClassName()] = $entity;
        }

        foreach ($this->workflowDefinitionEntities as $entity) {
            if (array_key_exists($entity->getClassName(), $newEntities)) {
                unset($newEntities[$entity->getClassName()]);
            } else {
                $this->workflowDefinitionEntities->removeElement($entity);
            }
        }

        foreach ($newEntities as $entity) {
            $entity->setWorkflowDefinition($this);
            $this->workflowDefinitionEntities->add($entity);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getWorkflowDefinitionEntities()
    {
        return $this->workflowDefinitionEntities;
    }

    /**
     * @param WorkflowDefinition $definition
     * @return $this
     */
    public function import(WorkflowDefinition $definition)
    {
        $this->setName($definition->getName())
            ->setLabel($definition->getLabel())
            ->setEnabled($definition->isEnabled())
            ->setConfiguration($definition->getConfiguration())
            ->setStartStep($definition->getStartStep())
            ->setWorkflowDefinitionEntities($definition->getWorkflowDefinitionEntities());

        return $this;
    }
}
