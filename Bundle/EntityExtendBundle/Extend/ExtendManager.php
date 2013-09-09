<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Metadata\MetadataFactory;

use Oro\Bundle\EntityExtendBundle\Metadata\ExtendClassMetadata;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
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
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    public function __construct(ConfigProvider $configProvider, MetadataFactory $metadataFactory)
    {
        $this->configProvider  = $configProvider;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @return ConfigProvider
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    /**
     * @param $className
     * @return bool
     */
    public function isExtend($className)
    {
        if (class_exists($className)) {
            /** @var ExtendClassMetadata $metadata */
            $metadata = $this->metadataFactory->getMetadataForClass($className);
            $isExtend = $metadata->isExtend;
        } else {
            $isExtend = $this->configProvider->getConfig($className)->is('is_extend');
        }

        return $isExtend;
    }

    /**
     * @param string $entityName
     * @param string $fieldName
     * @param string $fieldConfig
     * @param string $owner
     * @param string $mode
     */
    public function createField(
        $entityName,
        $fieldName,
        $fieldConfig,
        $owner = self::OWNER_CUSTOM,
        $mode = ConfigModelManager::MODE_DEFAULT
    ) {
        $configManager = $this->configProvider->getConfigManager();

        $configManager->createConfigFieldModel($entityName, $fieldName, $fieldConfig['type'], $mode);

        $extendFieldConfig = $this->configProvider->getConfig($entityName, $fieldName);
        $extendFieldConfig->set('owner', $owner);
        $extendFieldConfig->set('state', self::STATE_NEW);
        $extendFieldConfig->set('extend', true);

        if (isset($fieldConfig['options'])) {
            foreach ($fieldConfig['options'] as $key => $value) {
                $extendFieldConfig->set($key, $value);
            }
        }

        $configManager->persist($extendFieldConfig);
    }

    /**
     * @param string $entityName
     * @param bool   $isExtend
     * @param string $owner
     * @param string $mode
     */
    public function createEntity(
        $entityName,
        $isExtend = true,
        $owner = self::OWNER_CUSTOM,
        $mode = ConfigModelManager::MODE_DEFAULT
    ) {
        $configManager = $this->configProvider->getConfigManager();

        $configManager->createConfigEntityModel($entityName, $mode);

        $extendConfig = $this->configProvider->getConfig($entityName);
        $extendConfig->set('owner', $owner);
        $extendConfig->set('state', self::STATE_NEW);
        $extendConfig->set('is_extend', $isExtend);

        $configManager->persist($extendConfig);

        $entityFieldConfig = $configManager->getProvider('entity')->getConfig($entityName, 'id');
        $entityFieldConfig->set('label', 'Id');
    }
}
