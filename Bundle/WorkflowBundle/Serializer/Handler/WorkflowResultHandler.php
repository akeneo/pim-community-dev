<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Handler;

use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;

use Oro\Bundle\WorkflowBundle\Model\MetadataManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowResult;

class WorkflowResultHandler
{
    /**
     * @var MetadataManager $metadataManager
     */
    protected $metadataManager;

    /**
     * @param MetadataManager $metadataManager
     */
    public function __construct(MetadataManager $metadataManager)
    {
        $this->metadataManager = $metadataManager;
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param WorkflowResult $workflowResult
     * @param array $type
     * @param $context
     * @return array
     */
    public function workflowResultToJson(
        JsonSerializationVisitor $visitor,
        WorkflowResult $workflowResult,
        array $type,
        Context $context
    ) {
        $result = $this->convertToPlainArray($workflowResult->getValues());
        if (!$result) {
            return null;
        }
        return (object)$result;
    }

    /**
     * @param array|\Traversable $values
     * @return array
     */
    protected function convertToPlainArray($values)
    {
        $result = array();
        foreach ($values as $key => $value) {
            if (is_object($value) && $this->metadataManager->isManageableEntity($value)) {
                $result[$key] = $this->metadataManager->getEntityIdentifier($value);
            } elseif (is_array($value) || $value instanceof \Traversable) {
                $result[$key] = $this->convertToPlainArray($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
