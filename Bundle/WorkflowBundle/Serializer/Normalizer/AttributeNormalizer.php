<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

interface AttributeNormalizer
{
    /**
     * Normalizes a value of attribute into a scalar
     *
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @throws WorkflowException
     * @return mixed
     */
    public function normalize(Workflow $workflow, $attributeName, $attributeValue);

    /**
     * Denormalizes value of attribute back into it's model representation
     *
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return mixed
     */
    public function denormalize(Workflow $workflow, $attributeName, $attributeValue);

    /**
     * Supports normalization of attribute
     *
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return bool
     */
    public function supportsNormalization(Workflow $workflow, $attributeName, $attributeValue);

    /**
     * Supports denormalization of attribute
     *
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return bool
     */
    public function supportsDenormalization(Workflow $workflow, $attributeName, $attributeValue);
}
