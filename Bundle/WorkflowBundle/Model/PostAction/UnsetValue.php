<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class UnsetValue implements PostActionInterface
{
    /**
     * @var AssignValue
     */
    protected $assignValuePostAction;

    public function __construct(AssignValue $assignValuePostAction)
    {
        $this->assignValuePostAction = $assignValuePostAction;
    }

    /**
     * Execute post action.
     *
     * @param mixed $context
     */
    public function execute($context)
    {
        $this->assignValuePostAction->execute($context);
    }

    /**
     * Initialize post action based on passed options.
     *
     * @param array $options
     * @return PostActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        if (!isset($options['attribute']) && isset($options[0])) {
            $options[1] = null;
        } else {
            $options['value'] = null;
        }
        $this->assignValuePostAction->initialize($options);
    }
}
