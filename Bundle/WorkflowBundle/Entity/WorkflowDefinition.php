<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

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
     * @ORM\Column(name="managed_entity_class", type="string", length=255)
     */
    protected $managedEntityClass;

    /**
     * @var string
     *
     * @ORM\Column(name="start_step", type="string", length=255)
     */
    protected $startStep;

    /**
     * @var array
     *
     * @ORM\Column(name="configuration", type="array")
     */
    protected $configuration;

    /**
     * @var WorkflowDefinitionEntity[]|Collection
     *
     * @ORM\OneToMany(targetEntity="WorkflowDefinitionEntity", mappedBy="definition", cascade={"persist", "remove"})
     */
    protected $definitionEntities;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->configuration = array();
        $this->definitionEntities = new ArrayCollection();
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
     * Set managedEntityClass
     *
     * @param string $managedEntityClass
     * @return WorkflowDefinition
     */
    public function setManagedEntityClass($managedEntityClass)
    {
        $this->managedEntityClass = $managedEntityClass;

        return $this;
    }

    /**
     * Get managedEntityClass
     *
     * @return string
     */
    public function getManagedEntityClass()
    {
        return $this->managedEntityClass;
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
     * @param ArrayCollection|WorkflowDefinitionEntity[] $definitionEntities
     * @return WorkflowDefinition
     */
    public function setDefinitionEntities($definitionEntities)
    {
        if ($definitionEntities instanceof Collection) {
            $this->definitionEntities = $definitionEntities;
        } else {
            $this->definitionEntities = new ArrayCollection();
            foreach ($definitionEntities as $entity) {
                $this->definitionEntities->add($entity);
            }
        }

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinitionEntity[]
     */
    public function getDefinitionEntities()
    {
        return $this->definitionEntities;
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
            ->setManagedEntityClass($definition->getManagedEntityClass())
            ->setConfiguration($definition->getConfiguration())
            ->setStartStep($definition->getStartStep());

        return $this;
    }
}
