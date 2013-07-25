<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Proxy\ServiceProxy;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface;
use Oro\Bundle\EntityExtendBundle\Extend\Factory\ConfigFactory;
use Oro\Bundle\EntityExtendBundle\Tools\Generator\Generator;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class ExtendManager
{
    const STATE_NEW     = 'New';
    const STATE_UPDATED = 'Updated';
    const STATE_ACTIVE  = 'Active';

    const OWNER_SYSTEM = 'System';
    const OWNER_CUSTOM = 'Custom';

    /**
     * @var ProxyObjectFactory
     */
    protected $proxyFactory;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var ServiceProxy
     */
    protected $lazyEm;

    public function __construct(ServiceProxy $lazyEm, ConfigProvider $configProvider, $backend, $entityCacheDir)
    {
        $this->lazyEm         = $lazyEm;
        $this->configProvider = $configProvider;
        $this->proxyFactory   = new ProxyObjectFactory($this);
        $this->configFactory  = new ConfigFactory($this);
        $this->generator      = new Generator($configProvider, $backend, $entityCacheDir);
    }

    /**
     * @return ConfigProvider
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->lazyEm->getService();
    }

    /**
     * @return ProxyObjectFactory
     */
    public function getProxyFactory()
    {
        return $this->proxyFactory;
    }

    /**
     * @return ConfigFactory
     */
    public function getConfigFactory()
    {
        return $this->configFactory;
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
            && $this->configProvider->hasConfig($entityName)
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
        return $this->configProvider->getConfig($entityName)->get('extend_class');
    }

    /**
     * @param $entityName
     * @return null|string
     */
    public function getProxyClass($entityName)
    {
        return $this->configProvider->getConfig($entityName)->get('proxy_class');
    }

    /**
     * @param $entity
     */
    public function loadExtend($entity)
    {
        $proxy = $this->getProxyFactory()->getProxyObject($entity);
        $this->getProxyFactory()->initExtendObject($proxy);
    }

    /**
     * @param $entity
     */
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
