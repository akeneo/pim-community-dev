<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Abstract denormalizer class for flat entity denormalizers
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractEntityDenormalizer implements SerializerAwareInterface, DenormalizerInterface
{
    /** @var string */
    protected $entityClass;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var string[] */
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
        $object = null;
        if (is_array($data) && !empty($data)) {
            $object = $this->doDenormalize($data, $format, $context);
        } elseif (is_string($data) && strlen($data) > 0) {
            $object = $this->findEntity($data);
        }

        return $object;
    }

    /**
     * Get an existing entity (or create a new one)
     * Set all data values to entity
     *
     * @param mixed  $data
     * @param string $format
     * @param array  $context
     *
     * @return mixed
     */
    abstract protected function doDenormalize($data, $format, array $context);

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
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->managerRegistry->getRepository($this->entityClass);
    }

    /**
     * Get an entity from the context or from database
     *
     * @param array $data
     * @param array $context
     *
     * @throws InvalidArgumentException
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
                throw new InvalidArgumentException(
                    sprintf('Missing identifier "%s" to get "%s" identity', 'code', $this->entityClass)
                );
            }
        }

        return $entity;
    }

    /**
     * Instanciate entity from denormalizer entity class name
     *
     * @return object
     */
    protected function createEntity()
    {
        return new $this->entityClass();
    }

    /**
     * Find an entity from its identifier
     *
     * @param string $identifier
     *
     * @throws \LogicException
     *
     * @return object|false
     */
    protected function findEntity($identifier)
    {
        if (!$this->getRepository() instanceof IdentifiableObjectRepositoryInterface) {
            throw new \LogicException(
                sprintf(
                    'Repository "%s" does not implement ' .
                    '"Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface".',
                    get_class($this->getRepository())
                )
            );
        }

        $entity = $this->getRepository()->findOneByIdentifier($identifier);

        if (null === $entity) {
            throw new \LogicException(
                sprintf('Entity "%s" with identifier "%s" not found', $this->entityClass, $identifier)
            );
        }

        return $entity;
    }
}
