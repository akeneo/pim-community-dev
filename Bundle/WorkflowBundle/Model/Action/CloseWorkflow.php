<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class CloseWorkflow extends AbstractAction
{
    /**
     * Context accessor is not used
     */
    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function executeAction($context)
    {
        if (!$context instanceof WorkflowItem) {
            throw new InvalidParameterException('Close is available only for workflow items');
        }

        $context->setClosed(true);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (!empty($options)) {
            throw new InvalidParameterException('Close workflow options are not allowed');
        }
    }
}
