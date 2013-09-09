<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Metadata\MetadataFactory;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use Oro\Bundle\EntityExtendBundle\Entity\ProxyEntityInterface;
use Oro\Bundle\EntityExtendBundle\Metadata\ExtendClassMetadata;
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
     * @var MetadataFactory
     */
    protected $metadataFactory;

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

    public function __construct(ConfigProvider $configProvider, MetadataFactory $metadataFactory, Generator $generator)
    {
        $this->configProvider  = $configProvider;
        $this->metadataFactory = $metadataFactory;
        $this->generator       = $generator;
        $this->proxyFactory    = new ProxyObjectFactory($this);
        $this->extendFactory   = new ExtendFactory($this);
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
     * @param $className
     * @return bool
     */
    public function isExtend($className)
    {
        /** @var ExtendClassMetadata $metadata */
        $metadata = $this->metadataFactory->getMetadataForClass($className);

        return $metadata->isExtend;
    }

    /**
     * @param $className
     * @return null|string
     */
    public function getExtendClass($className)
    {
        return $this->generator->generateExtendClassName($className);
    }

    /**
     * @param $className
     * @return null|string
     */
    public function getProxyClass($className)
    {
        return $this->generator->generateProxyClassName($className);
    }

    public function newExtendEntity($className)
    {
        if ($this->isCustomEntity($className)) {
            $className = $this->getExtendClass($className);
        }
    }

    /**
     * @param $entity
     * @throws \InvalidArgumentException
     * @return ProxyEntityInterface
     */
    public function loadExtendEntity($entity)
    {
        if (!is_object($entity)) {
            if (!is_array($entity)
                || count($entity) != 2
                || !is_string($entity[0])
            ) {
                throw new \InvalidArgumentException('Invalid argument "\$entity"');
            }

            list($className, $criteria) = $entity;

            if ($this->isCustomEntity($className)) {
                $className = $this->getExtendClass($className);
            }

            $repo = $this->getEntityManager()->getRepository($className);

            if (is_array($criteria)) {
                $entity = $repo->findOneBy($criteria);
            } else {
                $entity = $repo->find($criteria);
            }
        }

        $proxy = $this->getProxyFactory()->getProxyObject($entity);
        $this->getProxyFactory()->initExtendObject($proxy);

        return $proxy;
    }

    /**
     * @param $className
     * @return bool
     */
    protected function isCustomEntity($className)
    {
        return $this->getConfigProvider()->getConfig($className)->is('owner', 'Custom');
    }
}
