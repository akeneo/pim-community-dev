<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface;
use Oro\Bundle\EntityExtendBundle\Tools\Generator\Generator;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class ExtendManager
{
    const STATE_NEW     = 'New';
    const STATE_UPDATED = 'Requires update';
    const STATE_ACTIVE  = 'Active';
    const STATE_DELETED = 'Deleted';

    const OWNER_SYSTEM = 'System';
    const OWNER_CUSTOM = 'Custom';

    /**
     * @var ProxyObjectFactory
     */
    protected $proxyFactory;

    /**
     * @var ExtendFactory
     */
    protected $extendFactory;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var OroEntityManager
     */
    protected $em;

    public function __construct(ConfigProvider $configProvider, Generator $generator)
    {
        $this->configProvider = $configProvider;
        $this->generator      = $generator;
        $this->proxyFactory   = new ProxyObjectFactory($this);
        $this->extendFactory  = new ExtendFactory($this);
    }

    /**
     * @return ConfigProvider
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    /**
     * @param OroEntityManager $em
     * @return $this
     */
    public function setEntityManager($em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * @return OroEntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @return ProxyObjectFactory
     */
    public function getProxyFactory()
    {
        return $this->proxyFactory;
    }

    /**
     * @return ExtendFactory
     */
    public function getExtendFactory()
    {
        return $this->extendFactory;
    }

    /**
     * @return Generator
     */
    public function getClassGenerator()
    {
        return $this->generator;
    }

    /**
     * @param $entityName
     * @return bool|string
     */
    public function isExtend($entityName)
    {
        if ($entityName
            && $this->configProvider->isConfigurable($entityName)
            && $this->configProvider->getConfig($entityName)->is('is_extend')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $entityName
     * @return null|string
     */
    public function getExtendClass($entityName)
    {
        return $this->generator->generateExtendClassName($entityName);
    }

    /**
     * @param $entityName
     * @return null|string
     */
    public function getProxyClass($entityName)
    {
        return $this->generator->generateProxyClassName($entityName);
    }

    /**
     * @param $entity
     */
    public function loadExtend($entity)
    {
        $proxy = $this->getProxyFactory()->getProxyObject($entity);
        $this->getProxyFactory()->initExtendObject($proxy);
    }

    public function persist($entity)
    {
        if ($this->isExtend($entity)) {
            $proxy = $this->getProxyFactory()->getProxyObject($entity);
            $proxy->__proxy__createFromEntity($entity);

            $this->getEntityManager()->detach($entity);
            $this->getEntityManager()->persist($proxy);
            $this->getEntityManager()->persist($proxy->__proxy__getExtend());
        }

        if ($entity instanceof ExtendProxyInterface) {
            $this->getEntityManager()->persist($entity->__proxy__getExtend());
        }
    }

    /**
     * @param $entity
     */
    public function remove($entity)
    {
        $extend = $this->getProxyFactory()->getProxyObject($entity);
        $this->getEntityManager()->remove($extend);
    }
}
