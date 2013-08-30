<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class CallMethod extends AbstractPostAction
{
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function execute($context)
    {
        if (!empty($this->options['object'])) {
            $callback = array($this->options['object'], $this->options['method']);
        } else {
            $callback = $this->options['method'];
        }
        $params = array_key_exists('method_parameters', $this->options) ? $this->options['method_parameters'] : array();

        $result = call_user_func_array($callback, $params);
        if (!empty($this->options['attribute'])) {
            $this->contextAccessor->setValue($context, $this->options['attribute'], $result);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['method'])) {
            throw new InvalidParameterException('Method name parameter is required');
        }

        $this->options = $options;

        return $this;
    }
}
