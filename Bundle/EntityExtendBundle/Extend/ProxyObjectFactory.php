<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface;

class ProxyObjectFactory
{
    /**
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @var ExtendProxyInterface[]
     */
    protected $proxyObjects = array();

    public function __construct(ExtendManager $extendManager)
    {
        $this->extendManager = $extendManager;
    }

    /**
     * @param $entity
     * @return null|ExtendProxyInterface
     */
    public function getProxyObject($entity)
    {
        if (isset($this->proxyObjects[spl_object_hash($entity)])) {
            return $this->proxyObjects[spl_object_hash($entity)];
        } else {
            return $this->createProxyObject($entity);
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
     * @return ExtendProxyInterface
     */
    protected function createProxyObject($entity)
    {
        $proxyClass = $this->extendManager->getProxyClass($entity);
        //$extend = $this->extendManager->getExtendFactory()->getExtendObject($entity);
        $proxy = new $proxyClass();

        $proxy->__proxy__createFromEntity($entity);

        return $this->proxyObjects[spl_object_hash($entity)] = $proxy;
    }
}
