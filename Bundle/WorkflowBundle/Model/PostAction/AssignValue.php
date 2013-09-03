<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Symfony\Component\PropertyAccess\PropertyPath;

class AssignValue extends AbstractPostAction
{
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $this->contextAccessor->setValue($context, $this->getAttribute(), $this->getValue());
    }

    /**
     * Get target.
     *
     * @return mixed
     */
    protected function getAttribute()
    {
        return array_key_exists('attribute', $this->options) ? $this->options['attribute'] : $this->options[0];
    }

    /**
     * Get value.
     *
     * @return mixed
     */
    protected function getValue()
    {
        return array_key_exists('value', $this->options) ? $this->options['value'] : $this->options[1];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (count($options) != 2) {
            throw new InvalidParameterException('Attribute and value parameters are required.');
        }

        $this->options = $options;

        if (!isset($options['attribute']) && !isset($options[0])) {
            throw new InvalidParameterException('Attribute must be defined.');
        }
        if (!array_key_exists('value', $options) && !array_key_exists(1, $options)) {
            throw new InvalidParameterException('Value must be defined.');
        }
        if (!($this->getAttribute() instanceof PropertyPath)) {
            throw new InvalidParameterException('Attribute must be valid property definition.');
        }

        return $this;
    }
}
