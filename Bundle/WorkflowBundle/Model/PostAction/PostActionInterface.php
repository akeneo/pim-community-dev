<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

interface PostActionInterface
{
    /**
     * Execute post action.
     *
     * @param mixed $context
     */
    public function execute($context);
}
