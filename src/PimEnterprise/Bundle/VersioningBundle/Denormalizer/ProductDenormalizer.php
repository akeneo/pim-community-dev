<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDenormalizer implements DenormalizerInterface
{
    /** @var string */
    protected $entityClass;

    /** @var ManagerRegistry */
    protected $managerRegsitry;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /**
     * @param string                    $entityClass
     * @param ManagerRegistry           $managerRegistry
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct($entityClass, ManagerRegistry $managerRegistry, PropertyAccessorInterface $propertyAccessor)
    {
        $this->entityClass     = $entityClass;
        $this->managerRegistry = $managerRegistry;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $object = $this->getEntity($data['sku']); //TODO Remove hardcoded stuff
        foreach ($data as $key => $value) {
            $this->propertyAccessor->setValue($object, $key, $value);
        }


        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === $this->entityClass;
    }

    /**
     * @return ReferableEntityRepositoryInterface
     */
    protected function getRepository()
    {
        return $this->managerRegistry->getRepository($this->entityClass);
    }


    protected function getEntity($code)
    {
        return $object = $this->findEntity($code) ?: $this->createEntity();
    }

    protected function createEntity()
    {
        return new $this->entityClass;
    }

    protected function findEntity($code)
    {
        return $this->getRepository()->findByReference($code);
    }

    protected function resetEntity()
    {

    }
}
