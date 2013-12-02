<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class CallMethod extends AbstractAction
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
        $object = $this->getObject($context);
        $method = $this->getMethod();
        if ($object) {
            $callback = array($object, $method);
        } else {
            $callback = $method;
        }

        $parameters = $this->getMethodParameters($context);

        $result = call_user_func_array($callback, $parameters);

        $attribute = $this->getAttribute();
        if ($attribute) {
            $this->contextAccessor->setValue($context, $attribute, $result);
        }
    }

    /**
     * @return PropertyPath|null
     */
    protected function getAttribute()
    {
        return $this->getOption($this->options, 'attribute', null);
    }

    /**
     * @return string
     */
    protected function getMethod()
    {
        return $this->options['method'];
    }

    /**
     * @param mixed $context
     * @return object|null
     */
    protected function getObject($context)
    {
        return !empty($this->options['object'])
            ? $this->contextAccessor->getValue($context, $this->options['object'])
            : null;
    }

    /**
     * @param mixed $context
     * @return array
     */
    protected function getMethodParameters($context)
    {
        $parameters = $this->getOption($this->options, 'method_parameters', array());

        foreach ($parameters as $name => $value) {
            $parameters[$name] = $this->contextAccessor->getValue($context, $value);
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['method'])) {
            throw new InvalidParameterException('Method name parameter is required');
        }

        if (!empty($options['object']) && !$options['object'] instanceof PropertyPath) {
            throw new InvalidParameterException('Object must be valid property definition');
        }

        $this->options = $options;

        return $this;
    }
}
