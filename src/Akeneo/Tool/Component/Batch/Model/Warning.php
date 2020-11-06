<?php

namespace Akeneo\Tool\Component\Batch\Model;

/**
 * Represents a Warning raised during step execution
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Warning
{
    /** @var integer */
    private $id;

    /** @var StepExecution */
    private $stepExecution;

    /** @var string */
    private $reason;

    /** @var array */
    private $reasonParameters = [];

    /** @var array */
    private $item;

    /**
     * Constructor
     *
     * @param StepExecution $stepExecution
     * @param string        $reason
     * @param array         $reasonParameters
     * @param array         $item
     */
    public function __construct(StepExecution $stepExecution, string $reason, array $reasonParameters, array $item)
    {
        $this->stepExecution = $stepExecution;
        $this->reason = $reason;
        $this->reasonParameters = $reasonParameters;
        $this->item = $item;
    }

    /**
     * Returns the id of the warning
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the step execution
     */
    public function getStepExecution(): \Akeneo\Tool\Component\Batch\Model\StepExecution
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
    public function setStepExecution(StepExecution $stepExecution): self
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * Returns the reason
     */
    public function getReason(): string
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
    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Returns the reason parameters
     */
    public function getReasonParameters(): array
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
    public function setReasonParameters(array $reasonParameters): self
    {
        $this->reasonParameters = $reasonParameters;

        return $this;
    }

    /**
     * Returns the item over which the warning is set
     */
    public function getItem(): array
    {
        return $this->item;
    }

    /**
     * Sets the item over which the warning is set
     *
     * @param array $item
     *
     * @return $this
     */
    public function setItem(array $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Returns a representation of the warning as an array
     */
    public function toArray(): array
    {
        return [
            'reason'           => $this->reason,
            'reasonParameters' => $this->reasonParameters,
            'item'             => $this->item,
        ];
    }
}
