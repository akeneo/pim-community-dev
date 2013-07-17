<?php

namespace Oro\Bundle\WorkflowBundle\Model;

class Step
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $attributes;

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
    protected $isFinal;

    /**
     * @var array
     */
    protected $allowedTransitions;

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
     * Set attributes.
     *
     * @param array $attributes
     * @return Step
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Get attributes.
     *
     * @return array
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

    /**
     * Check transition is allowed for current step.
     *
     * @param string $transitionName
     * @return bool
     */
    public function isTransitionAllowed($transitionName)
    {
        return in_array($transitionName, $this->allowedTransitions);
    }
}
