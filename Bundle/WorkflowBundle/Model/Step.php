<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\WorkflowBundle\Model\StepAttribute;

class Step
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var int
     */
    protected $order;

    /**
     * @var boolean
     */
    protected $isFinal = false;

    /**
     * @var ArrayCollection
     */
    protected $attributes;

    /**
     * @var string[]
     */
    protected $allowedTransitions = array();

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * Set allowed transitions.
     *
     * @param array $allowedTransitions
     * @return Step
     */
    public function setAllowedTransitions($allowedTransitions)
    {
        $this->allowedTransitions = $allowedTransitions;
        return $this;
    }

    /**
     * Get allowed transitions.
     *
     * @return array
     */
    public function getAllowedTransitions()
    {
        return $this->allowedTransitions;
    }

    /**
     * Check transition is allowed for current step.
     *
     * @param string $transitionName
     * @return bool
     */
    public function isAllowedTransition($transitionName)
    {
        return in_array($transitionName, $this->allowedTransitions);
    }

    /**
     * Allow transition.
     *
     * @param string $transitionName
     */
    public function allowTransition($transitionName)
    {
        if (!$this->isAllowedTransition($transitionName)) {
            $this->allowedTransitions[] = $transitionName;
        }
    }

    /**
     * Disallow transition.
     *
     * @param string $transitionName
     */
    public function disallowTransition($transitionName)
    {
        if ($this->isAllowedTransition($transitionName)) {
            array_splice($this->allowedTransitions, array_search($transitionName, $this->allowedTransitions), 1);
        }
    }

    /**
     * Set attributes.
     *
     * @param StepAttribute[] $attributes
     * @return Step
     */
    public function setAttributes(array $attributes)
    {
        $data = array();
        /** @var StepAttribute $attribute */
        foreach ($attributes as $attribute) {
            $data[$attribute->getName()] = $attribute;
        }
        unset($attributes);
        $this->attributes = new ArrayCollection($data);
        return $this;
    }

    /**
     * Get attributes.
     *
     * @return ArrayCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set step is final.
     *
     * @param boolean $isFinal
     * @return Step
     */
    public function setIsFinal($isFinal)
    {
        $this->isFinal = $isFinal;
        return $this;
    }

    /**
     * Check step  is final.
     *
     * @return boolean
     */
    public function getIsFinal()
    {
        return $this->isFinal;
    }

    /**
     * Set name.
     *
     * @param string $name
     * @return Step
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set order.
     *
     * @param int $order
     * @return Step
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set template.
     *
     * @param string $template
     * @return Step
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Get template.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
