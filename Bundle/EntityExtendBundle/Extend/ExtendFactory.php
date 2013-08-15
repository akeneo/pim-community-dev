<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ExtendFactory
{
    /**
     * @var ExtendManager
     */
    protected $extendManager;

    public function __construct(ExtendManager $extendManager)
    {
        $this->extendManager = $extendManager;
    }

    /**
     * @param string $entityName
     * @param string $fieldName
     * @param string $fieldConfig
     * @param string $mode
     */
    public function createField($entityName, $fieldName, $fieldConfig, $mode = ConfigModelManager::MODE_DEFAULT)
    {
        $configProvider = $this->extendManager->getConfigProvider();
        $configManager  = $this->extendManager->getConfigProvider()->getConfigManager();

        $configManager->createConfigFieldModel($entityName, $fieldName, $fieldConfig['type'], $mode);

        $extendFieldConfig = $configProvider->getConfig($entityName, $fieldName);
        $extendFieldConfig->set('owner', ExtendManager::OWNER_CUSTOM);
        $extendFieldConfig->set('state', ExtendManager::STATE_NEW);
        $extendFieldConfig->set('is_extend', true);

        if (isset($fieldConfig['options'])) {
            foreach ($fieldConfig['options'] as $key => $value) {
                $extendFieldConfig->set($key, $value);
            }
        }

        $configManager->persist($extendFieldConfig);
    }

    /**
     * @param string $entityName
     * @param string $mode
     */
    public function createEntity($entityName, $mode = ConfigModelManager::MODE_DEFAULT)
    {
        $configProvider = $this->extendManager->getConfigProvider();
        $configManager  = $this->extendManager->getConfigProvider()->getConfigManager();

        $configManager->createConfigEntityModel($entityName, $mode);

        $extendConfig = $configProvider->getConfig($entityName);
        $extendConfig->set('owner', ExtendManager::OWNER_CUSTOM);
        $extendConfig->set('state', ExtendManager::STATE_NEW);
        $extendConfig->set('is_extend', true);

        $configManager->persist($extendConfig);

        $configManager->createConfigFieldModel($entityName, 'id', 'integer');

        $extendFieldConfig = $configProvider->getConfig($entityName, 'id');
        $extendFieldConfig->set('owner', ExtendManager::OWNER_CUSTOM);
        $extendFieldConfig->set('state', ExtendManager::STATE_NEW);
        $extendFieldConfig->set('is_extend', true);

        $entityFieldConfig = $configManager->getProvider('entity')->getConfig($entityName, 'id');
        $entityFieldConfig->set('label', 'Id');
    }
}
