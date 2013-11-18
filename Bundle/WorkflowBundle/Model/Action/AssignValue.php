<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Symfony\Component\PropertyAccess\PropertyPath;

class AssignValue extends AbstractAction
{
    /**
     * @var array
     */
    protected $assigns = array();

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        foreach ($this->assigns as $assignOptions) {
            $this->contextAccessor->setValue(
                $context,
                $this->getAttribute($assignOptions),
                $this->getValue($assignOptions)
            );
        }
    }

    /**
     * Get target.
     *
     * @param array $options
     * @return mixed
     */
    protected function getAttribute(array $options)
    {
        return array_key_exists('attribute', $options) ? $options['attribute'] : $options[0];
    }

    /**
     * Get value.
     *
     * @param array $options
     * @return mixed
     */
    protected function getValue(array $options)
    {
        return array_key_exists('value', $options) ? $options['value'] : $options[1];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if ($this->isMassAssign($options)) {
            foreach ($options as $assignOptions) {
                $this->addAssign($assignOptions);
            }
        } else {
            $this->addAssign($options);
        }

        return $this;
    }

    /**
     * @param array $options
     * @throws InvalidParameterException
     */
    protected function addAssign(array $options)
    {
        if (count($options) != 2) {
            throw new InvalidParameterException('Attribute and value parameters are required.');
        }

        if (!isset($options['attribute']) && !isset($options[0])) {
            throw new InvalidParameterException('Attribute must be defined.');
        }
        if (!array_key_exists('value', $options) && !array_key_exists(1, $options)) {
            throw new InvalidParameterException('Value must be defined.');
        }
        if (!($this->getAttribute($options) instanceof PropertyPath)) {
            throw new InvalidParameterException('Attribute must be valid property definition.');
        }

        $this->assigns[] = $options;
    }

    /**
     * @param array $options
     * @return bool
     */
    protected function isMassAssign(array $options)
    {
        if (empty($options)) {
            return false;
        }

        foreach ($options as $element) {
            if (!is_array($element)) {
                return false;
            }
        }

        return true;
    }
}
