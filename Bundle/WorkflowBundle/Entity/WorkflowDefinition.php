<?php

namespace Oro\Bundle\WorkflowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation\Loggable as Oro;

/**
 * Definition of Workflow
 *
 * @ORM\Table(name="oro_workflow_definition")
 * @ORM\Entity(repositoryClass="Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowDefinitionRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
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
     * @ORM\Column(name="configuration", type="blob")
     */
    protected $configuration;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = false;
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
     * @param string $configuration
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
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
