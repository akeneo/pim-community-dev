<?php

namespace Oro\Bundle\EntityExtendBundle\Tools;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Mapping\ExtendClassMetadataFactory;

class ExtendConfigDumper
{
    const ENTITY = 'Extend\\Entity\\';
    const PREFIX = 'field_';

    /**
     * @var string
     */
    protected $backupDir;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var OroEntityManager
     */
    protected $em;

    /**
     * @param OroEntityManager $em
     * @param string           $backupDir
     * @param string           $cacheDir
     */
    public function __construct(OroEntityManager $em, $backupDir, $cacheDir)
    {
        $this->backupDir = $backupDir;
        $this->cacheDir  = $cacheDir;
        $this->em        = $em;
    }

    public function dump()
    {
        $yml = array();

        $configs = $this->em->getExtendManager()->getConfigProvider()->getConfigs();
        foreach ($configs as $config) {
            if ($config->is('is_extend')) {
                $yml[] = $this->dumpByConfig($config);
            }
        }

        file_put_contents(
            $this->backupDir . '/dump.yml',
            Yaml::dump($yml, 6)
        );

        /** @var ExtendClassMetadataFactory $metadataFactory */
        $metadataFactory = $this->em->getMetadataFactory();
        $metadataFactory->clearCache();
    }

    public function clear()
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->backupDir . '/dump.yml')) {
            $filesystem->remove(array($this->backupDir . '/dump.yml'));
        }

        if ($filesystem->exists($this->cacheDir)) {
            $filesystem->remove(array($this->cacheDir));
        }

        /** @var ExtendClassMetadataFactory $metadataFactory */
        $metadataFactory = $this->em->getMetadataFactory();
        $metadataFactory->clearCache();
    }

    protected function dumpByConfig(ConfigInterface $entityConfig)
    {
        $configProvider = $this->em->getExtendManager()->getConfigProvider();
        $className      = $entityConfig->getId()->getClassName();

        if (strpos($className, self::ENTITY) !== false) {
            $entityName = $className;
            $type       = 'Custom';
            $doctrine   = array(
                $entityName => array(
                    'type'       => 'entity',
                    'table'      => 'oro_extend_' . strtolower(str_replace('\\', '', $entityName)),
                    'fields'     => array(
                        'id' => array(
                            'type'      => 'integer',
                            'id'        => true,
                            'generator' => array(
                                'strategy' => 'AUTO'
                            )
                        )
                    ),
                    'oneToMany'  => array(),
                    'manyToOne'  => array(),
                    'manyToMany' => array(),
                )
            );
        } else {
            $entityName = $entityConfig->get('extend_class');
            $type       = 'Extend';
            $doctrine   = array(
                $entityName => array(
                    'type'       => 'mappedSuperclass',
                    'fields'     => array(),
                    'oneToMany'  => array(),
                    'manyToOne'  => array(),
                    'manyToMany' => array(),
                )
            );
        }

        $entityState = $entityConfig->get('state');

        $properties = array();
        if ($fieldConfigs = $configProvider->getConfigs($className)) {
            foreach ($fieldConfigs as $fieldConfig) {
                if ($fieldConfig->is('extend')) {
                    $fieldName              = self::PREFIX . $fieldConfig->getId()->getFieldName();
                    $fieldType              = $fieldConfig->getId()->getFieldType();
                    $properties[$fieldName] = $fieldConfig->getId()->getFieldName();

                    $doctrine[$entityName]['fields'][$fieldName]['code']     = $fieldName;
                    $doctrine[$entityName]['fields'][$fieldName]['type']     = $fieldType;
                    $doctrine[$entityName]['fields'][$fieldName]['nullable'] = true;

                    $doctrine[$entityName]['fields'][$fieldName]['length']    = $fieldConfig->get('length');
                    $doctrine[$entityName]['fields'][$fieldName]['precision'] = $fieldConfig->get('precision');
                    $doctrine[$entityName]['fields'][$fieldName]['scale']     = $fieldConfig->get('scale');
                }

                if ($fieldConfig->get('state') != ExtendManager::STATE_DELETED) {
                    $fieldConfig->set('state', ExtendManager::STATE_ACTIVE);
                }

                if ($fieldConfig->get('state') == ExtendManager::STATE_DELETED) {
                    $fieldConfig->set('is_deleted', true);
                }

                $configProvider->persist($fieldConfig);
            }
        }

        $configProvider->flush();

        $entityConfig->set('state', $entityState);
        if ($entityConfig->get('state') == ExtendManager::STATE_DELETED) {
            $entityConfig->set('is_deleted', true);
        } else {
            $entityConfig->set('state', ExtendManager::STATE_ACTIVE);
        }

        $configProvider->persist($entityConfig);

        $result = array(
            'class'    => $className,
            'entity'   => $entityName,
            'type'     => $type,
            'property' => $properties,
            'doctrine' => $doctrine,
        );

        if ($type == 'Extend') {
            $result['parent']  = get_parent_class($className);
            $result['inherit'] = get_parent_class($result['parent']);
        }

        $configProvider->flush();

        return $result;
    }
}
 