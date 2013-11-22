<?php

namespace Pim\Bundle\CustomEntityBundle\Manager;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Base implementation for ORM managers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmManager implements OrmManagerInterface
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Constructor
     * 
     * @param RegistryInterface         $doctrine
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(RegistryInterface $doctrine, PropertyAccessorInterface $propertyAccessor)
    {
        $this->doctrine = $doctrine;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function create($entityClass, array $defaultValues = array(), array $options = array())
    {
        $object = new $entityClass;
        foreach ($defaultValues as $propertyPath => $value) {
            $this->propertyAccessor->setValue($object, $propertyPath, $value);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function find($entityClass, $id, array $options = array())
    {
        return $this->doctrine->getRepository($entityClass)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($entityClass, array $options = array())
    {
        $method = isset($options['query_builder_method']) ? $options['query_builder_method'] : 'createQueryBuilder';
        $alias = isset($options['query_builder_alias']) ? $options['query_builder_alias'] : 't';

        return $this->doctrine->getRepository($entityClass)->$method($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity)
    {
        $em = $this->doctrine->getManager();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($entity)
    {
        $em = $this->doctrine->getManager();
        $em->remove($entity);
        $em->flush();
    }
}
