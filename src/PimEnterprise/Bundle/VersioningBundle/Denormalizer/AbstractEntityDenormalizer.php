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

    /** @var array */
    protected $supportedFormats = array('csv');

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
        if (is_array($data) && !empty($data)) {
            return $this->doNormalize($data, $format, $context);
        } elseif (is_string($data) && strlen($data) > 0) {
            return $this->findEntity($data);
        } else {
            return null;
        }
    }

    /**
     * Get an existing entity (or create a new one)
     * Set all data values to entity
     *
     * @param array $data
     * @param array $context
     *
     * @return mixed
     */
    abstract protected function doDenormalize(array $data, $format, array $context);

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === $this->entityClass && in_array($format, $this->supportedFormats);
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
    protected function getEntity(array $data, array $context)
    {
        if (isset($context['entity'])) {
            $entity = $context['entity'];
            unset($context['entity']);
        } else {
            if (isset($data['code'])) {
                $entity = $this->findEntity($data['code']);
            } else {
                throw new \Exception(
                    sprintf('Missing identifier "%s" to get "%s" identity', 'code', $this->entityClass)
                );
            }
        }

        return $entity;
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
