<?php

namespace Oro\Bundle\EntityConfigBundle;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;

use Metadata\MetadataFactory;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\EntityConfigBundle\Cache\CacheInterface;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;

use Oro\Bundle\EntityConfigBundle\Event\EntityConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

class ConfigBackendManager
{
    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var CacheInterface
     */
    protected $configCache;

    /**
     * @param MetadataFactory    $metadataFactory
     * @param ContainerInterface $container
     */
    public function __construct(MetadataFactory $metadataFactory, ContainerInterface $container)
    {
        $this->metadataFactory = $metadataFactory;
        $this->container       = $container;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->configCache = $cache;
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return EventDispatcher
     */
    public function dispatcher()
    {
        return $this->container->get('event_dispatcher');
    }

    /**
     * @param $className
     * @return EntityConfig
     */
    public function getConfig($className)
    {
        if (!$this->metadataFactory->getMetadataForClass($className) || !$this->isSchemaSynced()) {
            return null;
        }

        if (null !== $this->configCache
            && $config = $this->configCache->loadConfigFromCache($className)
        ) {
            return $config;
        } else {
            $entityConfigRepo = $this->em()->getRepository(ConfigEntity::ENTITY_NAME);
            /** @var ConfigEntity $entity */
            $entity = $entityConfigRepo->findOneBy(array('className' => $className));
            if ($entity) {
                $config = EntityConfig::fromEntity($entity);

                if (null !== $this->configCache) {
                    $this->configCache->putConfigInCache($config);
                }
                return $config;
            } else {
                return null;
            }
        }
    }

    public function update()
    {
        /** @var $doctrineMetadata ClassMetadata */
        foreach ($this->em()->getMetadataFactory()->getAllMetadata() as $doctrineMetadata) {
            if ($this->metadataFactory->getMetadataForClass($doctrineMetadata->getName())
                && !$this->em()->getRepository(ConfigEntity::ENTITY_NAME)->findOneBy(array(
                    'className' => $doctrineMetadata->getName()))
            ) {
                $entity = new ConfigEntity();
                $entity->setClassName($doctrineMetadata->getName());

                // create config from entity
                $config = EntityConfig::fromEntity($entity);
                // listeners can change config
                $this->dispatcher()->dispatch(Events::prePersistEntityConfig, new EntityConfigEvent($config, $this));
                // then we copy changes form entity to config
                $config->toEntity($entity);
                // end persist entity
                $this->em()->persist($entity);
            }
        }

        $this->em()->flush();
    }

    /**
     * @return bool
     */
    protected function isSchemaSynced()
    {
        $tables = $this->em()->getConnection()->getSchemaManager()->listTableNames();
        $table  = $this->em()->getClassMetadata(ConfigEntity::ENTITY_NAME)->getTableName();

        return in_array($table, $tables);
    }
}
