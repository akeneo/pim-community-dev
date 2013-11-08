<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Event\NewFieldConfigModelEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConfigProvider
     */
    protected $extendConfigProvider;

    /**
     * @var  ExtendManager
     */
    protected $extendManager;

    /**
     * @param ExtendManager $extendManager
     */
    public function __construct(ExtendManager $extendManager)
    {
        $this->extendConfigProvider = $extendManager->getConfigProvider();
        $this->extendManager        = $extendManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PRE_PERSIST_CONFIG      => 'persistConfig',
            Events::NEW_ENTITY_CONFIG_MODEL => 'newEntity',
            Events::NEW_FIELD_CONFIG_MODEL  => 'newField',
        ];
    }

    /**
     * @param PersistConfigEvent $event
     *
     * @todo as discussed with Alpha team thm method will be refactored in this sprint
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function persistConfig(PersistConfigEvent $event)
    {
        $change    = $event->getConfigManager()->getConfigChangeSet($event->getConfig());
        $scope     = $event->getConfig()->getId()->getScope();
        $className = $event->getConfig()->getId()->getClassName();

        if ($scope == 'extend'
            && $event->getConfig()->getId() instanceof FieldConfigId
            && $event->getConfig()->is('owner', ExtendManager::OWNER_CUSTOM)
            && count(array_intersect_key(array_flip(['length', 'precision', 'scale', 'state']), $change))
        ) {
            $entityConfig = $event->getConfigManager()->getProvider($scope)->getConfig($className);

            if ($event->getConfig()->get('state') != ExtendManager::STATE_NEW
                && $event->getConfig()->get('state') != ExtendManager::STATE_DELETED
                && !isset($change['state'])
            ) {
                $event->getConfig()->set('state', ExtendManager::STATE_UPDATED);
                $event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
            }

            /**
             * Relations case
             */
            if ($event->getConfig()->get('state') == ExtendManager::STATE_NEW
                && in_array($event->getConfig()->getId()->getFieldType(), ['oneToMany', 'manyToOne', 'manyToMany'])
            ) {
                $this->createRelation($event->getConfig());
            }

            if ($entityConfig->get('state') != ExtendManager::STATE_NEW) {
                $entityConfig->set('state', ExtendManager::STATE_UPDATED);

                $event->getConfigManager()->persist($entityConfig);
            }
        }

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $event->getConfigManager()->getProvider('extend');
        $extendFieldConfig    = $extendConfigProvider->getConfigById($event->getConfig()->getId());

        if ($scope == 'datagrid'
            && $event->getConfig()->getId() instanceof FieldConfigId
            && !in_array($event->getConfig()->getId()->getFieldType(), ['text'])
            && $extendFieldConfig->is('is_extend')
            && isset($change['is_visible'])
        ) {
            $index        = [];
            $extendConfig = $extendConfigProvider->getConfig($className);
            if ($extendConfig->has('index')) {
                $index = $extendConfig->get('index');
            }

            $index[$event->getConfig()->getId()->getFieldName()] = $event->getConfig()->get('is_visible');

            $extendConfig->set('index', $index);

            $event->getConfigManager()->persist($extendConfig);
        }
    }

    /**
     * @param NewEntityConfigModelEvent $event
     */
    public function newEntity(NewEntityConfigModelEvent $event)
    {
        $originalClassName       = $event->getClassName();
        $originalParentClassName = get_parent_class($originalClassName);

        $parentClassArray = explode('\\', $originalParentClassName);
        $classArray       = explode('\\', $originalClassName);

        $parentClassName = array_pop($parentClassArray);
        $className       = array_pop($classArray);

        if ($parentClassName == 'Extend' . $className) {
            $config = $event->getConfigManager()->getProvider('extend')->getConfig($event->getClassName());
            $config->set('is_extend', true);
            $config->set('extend_class', ExtendConfigDumper::ENTITY . $parentClassName);

            $event->getConfigManager()->persist($config);
        }
    }

    /**
     * @param NewFieldConfigModelEvent $event
     */
    public function newField(NewFieldConfigModelEvent $event)
    {
        /** @var ConfigProvider $configProvider */
        $configProvider = $event->getConfigManager()->getProvider('extend');

        $entityConfig = $configProvider->getConfig($event->getClassName());
        if ($entityConfig->is('upgradeable', false)) {
            $entityConfig->set('upgradeable', true);
            $configProvider->persist($entityConfig);
        }
    }

    protected function createRelation(Config $fieldConfig)
    {
        $selfConfig = $this->extendConfigProvider->getConfig($fieldConfig->getId()->getClassName());
        $relations  = $selfConfig->get('relation');
        foreach ($relations as $relation) {
            if ($relation['field_id'] == $fieldConfig->getId()) {
                return;
            }
        }

        if ($fieldConfig->is('relation_key')) {
            $targetConfig = $this->extendConfigProvider->getConfig($fieldConfig->get('target_entity'));
            $relations    = $targetConfig->get('relation');
            if (isset($relations[$fieldConfig->get('relation_key')])) {
                $this->createTargetRelation($fieldConfig, $fieldConfig->get('relation_key'));
            }
        }

        $this->createSelfRelation($fieldConfig);
    }

    protected function createSelfRelation(Config $fieldConfig)
    {
        $selfEntityClass   = $fieldConfig->getId()->getClassName();
        $targetEntityClass = $fieldConfig->get('target_entity');
        $selfFieldType     = $fieldConfig->getId()->getFieldType();
        $selfFieldName     = $fieldConfig->getId()->getFieldName();
        $selfConfig        = $this->extendConfigProvider->getConfig($selfEntityClass);
        $relationKey       = implode('|', [$selfFieldType, $selfEntityClass, $targetEntityClass, $selfFieldName]);
        $scope             = 'extend';

        /**
         * in case of oneToMany relation
         * automatically create target field (type: manyToOne)
         */
        $targetFieldId = false;
        $owner         = true;
        $targetOwner   = false;

        if (in_array($selfFieldType, ['oneToMany', 'manyToMany'])) {
            $classNameArray    = explode('\\', $selfEntityClass);
            $relationFieldName = strtolower(array_pop($classNameArray)) . '_' . $selfFieldName;

            if ($selfFieldType == 'oneToMany') {
                $owner       = false;
                $targetOwner = true;
            }

            $targetFieldId = new FieldConfigId(
                $targetEntityClass,
                $scope,
                $relationFieldName,
                ExtendHelper::getReversRelationType($selfFieldType)
            );
        }

        $selfRelationConfig = [
            'assign'          => false,
            'field_id'        => $fieldConfig->getId(),
            'owner'           => $owner,
            'target_entity'   => $targetEntityClass,
            'target_field_id' => $targetFieldId // for 1:*, create field
        ];

        $selfRelations                 = $selfConfig->get('relation') ? : [];
        $selfRelations[$relationKey] = $selfRelationConfig;

        $selfConfig->set('relation', $selfRelations);

        $this->extendConfigProvider->persist($selfConfig);

        $targetConfig = $this->extendConfigProvider->getConfig($targetEntityClass);

        $targetRelationConfig = [
            'assign'          => false,
            'field_id'        => $targetFieldId, // for 1:*, new created field
            'owner'           => $targetOwner,
            'target_entity'   => $selfEntityClass,
            'target_field_id' => $fieldConfig->getId(),
        ];

        $targetRelations               = $targetConfig->get('relation') ? : [];
        $targetRelations[$relationKey] = $targetRelationConfig;

        $targetConfig->set('relation', $targetRelations);
        $fieldConfig->set('relation_key', $relationKey);

        $this->extendConfigProvider->persist($targetConfig);
        //$this->extendConfigProvider->persist($fieldConfig);
    }

    protected function createTargetRelation(Config $fieldConfig, $relationKey)
    {
        $selfEntityClass   = $fieldConfig->getId()->getClassName();
        $targetEntityClass = $fieldConfig->get('target_entity');

        $selfConfig         = $this->extendConfigProvider->getConfig($selfEntityClass);
        $selfRelations      = $selfConfig->get('relation');
        $selfRelationConfig = &$selfRelations[$relationKey];

        $selfRelationConfig['field_id'] = $fieldConfig;

        $targetConfig         = $this->extendConfigProvider->getConfig($targetEntityClass);
        $targetRelations      = $targetConfig->get('relation');
        $targetRelationConfig = &$targetRelations[$relationKey];

        $targetRelationConfig['target_field_id'] = $fieldConfig;

        $selfConfig->set('relation', $selfRelations);
        $targetConfig->set('relation', $targetRelations);

        $this->extendConfigProvider->persist($targetConfig);
    }

    /**
     * @param ConfigInterface   $entityConfig
     * @param ConfigIdInterface $fieldConfigId
     * @param bool              $target
     * @return null|string
     */
    protected function findRelationKey(ConfigInterface $entityConfig, ConfigIdInterface $fieldConfigId, $target = false)
    {
        $relations = $entityConfig->get('relation');
        foreach ($relations as $key => $relation) {
            if ($relation[$target ? 'target_field_id' : 'field_id'] == $fieldConfigId) {
                return $key;
            }
        }

        return null;
    }
}
