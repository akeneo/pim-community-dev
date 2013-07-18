<?php

namespace Oro\Bundle\WorkflowBundle\Serializer;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData;

/**
 * Serializes and de-serializes WorkflowItemData
 */
interface WorkflowItemDataSerializerInterface
{
    /**
     * Serializes WorkflowItemData
     *
     * @param WorkflowItemData $data
     * @return string
     */
    public function serialize(WorkflowItemData $data);

    /**
     * De-serializes data into the WorkflowItemData.
     *
     * @param mixed $data
     * @return WorkflowItemData
     */
    public function deserialize($data);
}
