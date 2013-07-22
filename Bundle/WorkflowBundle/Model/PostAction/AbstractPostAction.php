<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

abstract class AbstractPostAction implements PostActionInterface
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * Constructor
     *
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(ContextAccessor $contextAccessor)
    {
        $this->contextAccessor = $contextAccessor;
    }
}
