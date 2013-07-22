<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

interface PostActionInterface
{
    /**
     * Execute post action.
     *
     * @param mixed $context
     */
    public function execute($context);

    /**
     * Initialize post action based on passed options.
     *
     * @param array $options
     * @return PostActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options);
}
