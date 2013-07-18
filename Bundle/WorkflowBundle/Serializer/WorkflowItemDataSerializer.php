<?php

namespace Oro\Bundle\WorkflowBundle\Serializer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData;
use Oro\Bundle\WorkflowBundle\Serializer\Normalizer\WorkflowItemDataNormalizer;

/**
 * Serializes and de-serializes WorkflowItemData
 */
class WorkflowItemDataSerializer implements WorkflowItemDataSerializerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $format = 'json';

    /**
     * Constructor
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(WorkflowItemData $data)
    {
        // @TODO WorkflowDefinition can configure data serialized data format ("json")
        return $this->serializer->serialize($data, $this->format);

    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data)
    {
        // @TODO WorkflowDefinition can configure custom data type (extended from WorkflowItemData)
        // @TODO WorkflowDefinition can configure data serialized data format ("json")
        return $this->serializer->deserialize(
            $data,
            'Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData',
            $this->format
        );
    }
}
