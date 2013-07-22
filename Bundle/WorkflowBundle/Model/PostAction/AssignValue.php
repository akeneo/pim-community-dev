<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class AssignValue extends AbstractPostAction
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
        $this->contextAccessor->setValue($context, $this->options[0], $this->options[1]);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (count($options) != 2) {
            throw new InvalidParameterException('Assignee and assigned parameters are required.');
        }
        $this->options = $options;
        return $this;
    }
}
