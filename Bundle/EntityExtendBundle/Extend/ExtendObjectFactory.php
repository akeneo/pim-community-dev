<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;

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
    public function getExtendObject($entity)
    {
        if (isset($this->extendObjects[spl_object_hash($entity)])) {
            return $this->extendObjects[spl_object_hash($entity)];
        } else {
            /**
             * TODO:
             */
            //return $this->createExtend($entity);
            return null;
        }
    }

    /**
     * @param $entity
     * @return bool
     */
    public function hasExtendObject($entity)
    {
        return isset($this->extendObjects[spl_object_hash($entity)]);
    }

    /**
     * @param $entity
     */
    public function removeExtendObject($entity)
    {
        if (isset($this->extendObjects[spl_object_hash($entity)])) {
            unset($this->extendObjects[spl_object_hash($entity)]);
        }
    }

    /**
     * @param $entity
     * @return null|ExtendEntityInterface
     */
    protected function createExtend($entity)
    {
        $extendClass = $this->extendManager->getConfigProvider()->getExtendClass($entity);
        $extend = $this->extendManager
            ->getEntityManager()
            ->getRepository($extendClass)
            ->findOneBy(array('parent' => $entity));

        if (!$extend) {
            /** @var ExtendEntityInterface $extend */
            $extend = new $extendClass();
            $extend->setParent($entity);
        }

        $this->extendManager->getEntityManager()->persist($extend);

        return $this->extendObjects[spl_object_hash($entity)] = $extend;
    }
}
