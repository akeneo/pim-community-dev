<?php

namespace Oro\Bundle\WorkflowBundle\Serializer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData;

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
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $format = 'json';

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Creates default serializer
     *
     * @return Serializer
     */
    protected function getSerializer()
    {
        if (!$this->serializer) {
            $normalizers = array(new GetSetMethodNormalizer());
            $encoders = array(new JsonEncoder());
            return new Serializer($normalizers, $encoders);
        }
        return $this->serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(WorkflowItemData $data)
    {
        // @TODO Use $em to serialize entities in $data
        // @TODO Maybe format ("json") can be configured via WorkflowItem
        return $this->serializer->serialize($data, $this->format);

    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data)
    {
        // @TODO Use $em to deserialize entities in $data
        // @TODO WorkflowItem can configure custom type (extended from WorkflowItemData)
        return $this->serializer->deserialize($data, 'Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData', $this->format);
    }
}
