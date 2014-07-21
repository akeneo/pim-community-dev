<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractEntityDenormalizer implements SerializerAwareInterface, DenormalizerInterface
{
    /** @var string */
    protected $entityClass;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $entityClass
     */
    public function __construct(ManagerRegistry $managerRegistry, $entityClass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->entityClass     = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return $this->getEntity($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === $this->entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return ReferableEntityRepositoryInterface
     */
    protected function getRepository()
    {
        return $this->managerRegistry->getRepository($this->entityClass);
    }

    /**
     * @param string $identifier
     *
     * @return object
     */
    protected function getEntity($identifier)
    {
        return $object = strlen($identifier) > 0 ? $this->findEntity($identifier) : $this->createEntity();
    }

    /**
     * @return object
     */
    protected function createEntity()
    {
        return new $this->entityClass;
    }

    /**
     * @param string $identifier
     *
     * @return object|false
     */
    protected function findEntity($identifier)
    {
        $entity = $this->getRepository()->findByReference($identifier);
        if (!$entity) {
            throw new \Exception(
                sprintf('Entity "%s" with identifier "%s" not found', $this->entityClass, $identifier)
            );
        }

        return $entity;
    }
}
