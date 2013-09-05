<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface;

class ProxyObjectFactory
{
    /**
     * @var ExtendProxyInterface[]
     */
    protected $proxyObjects = array();

    /**
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @param ExtendManager $extendManager
     */
    public function __construct(ExtendManager $extendManager)
    {
        $this->extendManager = $extendManager;
    }

    /**
     * @param $entity
     * @return ExtendProxyInterface
     */
    public function getProxyObject($entity)
    {
        if (isset($this->proxyObjects[spl_object_hash($entity)])) {
            return $this->proxyObjects[spl_object_hash($entity)];
        } else {
            return $this->initProxyObject($entity);
        }
    }

    /**
     * @param $entity
     * @return bool
     */
    public function hasProxyObject($entity)
    {
        return isset($this->proxyObjects[spl_object_hash($entity)]);
    }

    /**
     * @param $entity
     */
    public function removeProxyObject($entity)
    {
        if (isset($this->proxyObjects[spl_object_hash($entity)])) {
            unset($this->proxyObjects[spl_object_hash($entity)]);
        }
    }

    /**
     * @param $entity
     * @return null|ExtendEntityInterface
     */
    public function initExtendObject(ExtendProxyInterface $entity)
    {
        $entityClass = get_parent_class($entity);
        $extendClass = $this->extendManager->getExtendClass($entityClass);

        $em     = $this->extendManager->getEntityManager();
        $extend = $em->getUnitOfWork()->isEntityScheduled($entity)
            ? $em
                ->getRepository($extendClass)
                ->findOneBy(array('__extend__parent' => $em->getUnitOfWork()->getEntityIdentifier($entity)))
            : null;

        if (!$extend) {
            /** @var ExtendEntityInterface $extend */
            $extend = new $extendClass();
            $extend->__extend__setParent($entity);
        }

        $entity->__proxy__setExtend($extend);

        return $this->proxyObjects[spl_object_hash($entity)] = $extend;
    }

    /**
     * @param $entity
     * @return null|ExtendProxyInterface
     */
    protected function initProxyObject($entity)
    {
        if (!$entity instanceof ExtendProxyInterface) {
            $proxyClass = $this->extendManager->getProxyClass($entity);
            $proxy      = new $proxyClass();
            $proxy->__proxy__createFromEntity($entity);
            $entity = $proxy;
            $this->extendManager->getProxyFactory()->getProxyObject($entity);
        }

        return $entity;
    }
}
