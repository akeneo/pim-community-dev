<?php

namespace Oro\Bundle\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\WorkflowBundle\Model\Workflow;

interface WorkflowAwareSerializer extends SerializerInterface
{
    /**
     * @return Workflow
     */
    public function getWorkflow();

    /**
     * @return string
     */
    public function getWorkflowName();

    /**
     * @param string $name
     */
    public function setWorkflowName($name);
}
