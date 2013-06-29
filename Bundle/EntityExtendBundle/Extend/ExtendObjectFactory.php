<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface;

class ExtendObjectFactory
{
    /**
     * @var ExtendEntityInterface[]
     */
    protected $extendObjects = array();

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
     * @return null|ExtendEntityInterface
     */
    public function getExtendObject(ExtendProxyInterface $entity)
    {
        if (isset($this->extendObjects[spl_object_hash($entity)])) {
            return $this->extendObjects[spl_object_hash($entity)];
        } else {
            return $this->createExtend($entity);
        }
    }

    /**
     * @param $entity
     * @return bool
     */
    public function hasExtendObject(ExtendProxyInterface $entity)
    {
        return isset($this->extendObjects[spl_object_hash($entity)]);
    }

    /**
     * @param $entity
     */
    public function removeExtendObject(ExtendProxyInterface $entity)
    {
        if (isset($this->extendObjects[spl_object_hash($entity)])) {
            unset($this->extendObjects[spl_object_hash($entity)]);
        }
    }

    /**
     * @param $entity
     * @return null|ExtendEntityInterface
     */
    protected function createExtend(ExtendProxyInterface $entity)
    {
        $entityClass = get_parent_class($entity);
        $extendClass = $this->extendManager->getExtendClass($entityClass);

        $em = $this->extendManager->getEntityManager();
        $extend = $em
            ->getRepository($extendClass)
            ->findOneBy(array('__extend__parent' => $em->getUnitOfWork()->getEntityIdentifier($entity)));

        if (!$extend) {
            /** @var ExtendEntityInterface $extend */
            $extend = new $extendClass();
            $extend->__extend__setParent($entity);
        }

        $entity->__proxy__setExtend($extend);

        $this->extendManager->getEntityManager()->persist($extend);

        return $this->extendObjects[spl_object_hash($entity)] = $extend;
    }
}
