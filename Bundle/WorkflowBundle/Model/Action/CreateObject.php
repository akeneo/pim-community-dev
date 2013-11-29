<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class CreateObject extends AbstractAction
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
        $object = $this->createObject($context);
        $this->contextAccessor->setValue($context, $this->options['attribute'], $object);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['class'])) {
            throw new InvalidParameterException('Class name parameter is required');
        }

        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Attribute name parameter is required');
        }
        if (!$options['attribute'] instanceof PropertyPath) {
            throw new InvalidParameterException('Attribute must be valid property definition.');
        }

        if (!empty($options['data']) && !is_array($options['data'])) {
            throw new InvalidParameterException('Object data must be an array.');
        }

        if (!empty($options['arguments']) && !is_array($options['arguments'])) {
            throw new InvalidParameterException('Object constructor arguments must be an array.');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @param mixed $context
     * @return object
     */
    protected function createObject($context)
    {
        $objectClassName = $this->getObjectClassName();

        $arguments = $this->getConstructorArguments();
        if ($arguments) {
            $reflection = new \ReflectionClass($objectClassName);
            $object = $reflection->newInstanceArgs($arguments);
        } else {
            $object = new $objectClassName();
        }

        $objectData = $this->getObjectData();
        if ($objectData) {
            $this->assignObjectData($context, $object, $objectData);
        }

        return $object;
    }

    /**
     * @param mixed $context
     * @param object $entity
     * @param array $parameters
     */
    protected function assignObjectData($context, $entity, array $parameters)
    {
        foreach ($parameters as $parameterName => $valuePath) {
            $parameterValue = $this->contextAccessor->getValue($context, $valuePath);
            $this->contextAccessor->setValue($entity, $parameterName, $parameterValue);
        }
    }

    /**
     * @return string
     */
    protected function getObjectClassName()
    {
        return $this->options['class'];
    }

    /**
     * @return array
     */
    protected function getObjectData()
    {
        return $this->getOption($this->options, 'data', array());
    }

    /**
     * @return array
     */
    protected function getConstructorArguments()
    {
        return $this->getOption($this->options, 'arguments', array());
    }
}
