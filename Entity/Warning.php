<?php

namespace Akeneo\Bundle\BatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a Warning raised during step execution
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Warning
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var StepExecution
     *
     * @ORM\ManyToOne(targetEntity="StepExecution", inversedBy="warnings")
     * @ORM\JoinColumn(name="step_execution_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $stepExecution;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text", nullable=true)
     */
    private $reason;

    /**
     * @ORM\Column(name="reason_parameters", type="array", nullable=false)
     * @var array
     */
    private $reasonParameters = [];

    /**
     * @ORM\Column(name="item", type="array", nullable=false)
     * @var array
     */
    private $item;

    /**
     * Constructor
     *
     * @param StepExecution $stepExecution
     * @param string        $name
     * @param string        $reason
     * @param array         $reasonParameters
     * @param array         $item
     */
    function __construct(StepExecution $stepExecution, $name, $reason, array $reasonParameters, array $item)
    {
        $this->stepExecution = $stepExecution;
        $this->name = $name;
        $this->reason = $reason;
        $this->reasonParameters = $reasonParameters;
        $this->item = $item;
    }

    /**
     * Returns the id of the warning
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the step execution
     * 
     * @return StepExecution
     */
    public function getStepExecution()
    {
        return $this->stepExecution;
    }

    /**
     * Sets the step execution
     * 
     * @param StepExecution $stepExecution
     *
     * @return $this
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * Returns the name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     * 
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the reason
     * 
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Sets the reason
     *
     * @param string $reason
     *
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Returns the reason parameters
     * 
     * @return array
     */
    public function getReasonParameters()
    {
        return $this->reasonParameters;
    }

    /**
     * Sets  the reason parameters
     * 
     * @param array $reasonParameters
     *
     * @return $this
     */
    public function setReasonParameters(array $reasonParameters)
    {
        $this->reasonParameters = $reasonParameters;

        return $this;
    }

    /**
     * Returns the item over which the warning is set
     * 
     * @return array
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Sets the item over which the warning is set
     *
     * @param type $item
     *
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }
}
