<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Event\NewFieldConfigModelEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;

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
        return array(
            Events::PRE_PERSIST_CONFIG      => 'persistConfig',
            Events::NEW_ENTITY_CONFIG_MODEL => 'newEntity',
            Events::NEW_FIELD_CONFIG_MODEL  => 'newField',
        );
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function persistConfig(PersistConfigEvent $event)
    {
        $change = $event->getConfigManager()->getConfigChangeSet($event->getConfig());

        $scope     = $event->getConfig()->getId()->getScope();
        $className = $event->getConfig()->getId()->getClassName();

        if ($scope == 'extend'
            && $event->getConfig()->getId() instanceof FieldConfigId
            && $event->getConfig()->is('owner', ExtendManager::OWNER_CUSTOM)
            && count(array_intersect_key(array_flip(array('length', 'precision', 'scale', 'state')), $change))
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
                && in_array($event->getConfig()->getId()->getFieldType(), array('oneToMany', 'manyToOne', 'manyToMany'))
            ) {
                $this->createRelation($event->getConfig());
            }

            if ($entityConfig->get('state') != ExtendManager::STATE_NEW) {
                $entityConfig->set('state', ExtendManager::STATE_UPDATED);

                $event->getConfigManager()->persist($entityConfig);
            }
        }

        if ($scope == 'datagrid'
            && $event->getConfig()->getId() instanceof FieldConfigId
            && !in_array($event->getConfig()->getId()->getFieldType(), array('text'))
            && isset($change['is_visible'])

        ) {
            /** @var ConfigProvider $extendConfigProvider */
            $extendConfigProvider = $event->getConfigManager()->getProvider('extend');
            $extendConfig         = $extendConfigProvider->getConfig($className);
            $index                = $extendConfig->has('index') ? $extendConfig->get('index') : array();

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
        if ($this->findRelation($selfConfig, $fieldConfig->getId())) {
            return;
        }

        $targetConfig = $this->extendConfigProvider->getConfig($fieldConfig->get('target_entity'));
        if ($this->findRelation($targetConfig, $fieldConfig->getId(), true)) {
            //$this->createTargetRelation($fieldConfig);

            return;
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
        $scope             = 'extend';

        /**
         * in case of oneToMany relation
         * automatically create target field (type: manyToOne)
         */
        $targetFieldId = false;
        $owner         = true;
        $targetOwner   = false;

        if (in_array($selfFieldType, array('oneToMany', 'manyToMany'))) {
            $classNameArray    = explode('\\', $selfEntityClass);
            $relationFieldName = strtolower(array_pop($classNameArray)) . '_' . $selfFieldName;

            if ($selfFieldType == 'oneToMany') {
                $owner             = false;
                $targetOwner       = true;
            }

            $targetFieldId = new FieldConfigId(
                $targetEntityClass,
                $scope,
                $relationFieldName,
                ExtendHelper::getReversRelationType($selfFieldType)
            );
        }

        $selfRelationConfig = array(
            'assign'          => false,
            'field_id'        => $fieldConfig->getId(),
            'owner'           => $owner,
            'target_entity'   => $targetEntityClass,
            'target_field_id' => $targetFieldId // for 1:*, create field
        );

        $selfRelations   = $selfConfig->get('relation') ? : array();
        $selfRelations[] = $selfRelationConfig;

        $selfConfig->set('relation', $selfRelations);

        $this->extendConfigProvider->persist($selfConfig);

        $targetConfig = $this->extendConfigProvider->getConfig($targetEntityClass);

        $targetRelationConfig = array(
            'assign'          => false,
            'field_id'        => $targetFieldId, // for 1:*, new created field
            'owner'           => $targetOwner,
            'target_entity'   => $selfEntityClass,
            'target_field_id' => $fieldConfig->getId(),
        );

        $targetRelations   = $targetConfig->get('relation') ? : array();
        $targetRelations[] = $targetRelationConfig;

        $targetConfig->set('relation', $targetRelations);

        $this->extendConfigProvider->persist($targetConfig);
    }

    protected function createTargetRelation(Config $fieldConfig)
    {
        $selfEntityClass   = $fieldConfig->getId()->getClassName();
        $targetEntityClass = $fieldConfig->get('target_entity');
        $selfFieldType     = $fieldConfig->getId()->getFieldType();
        $selfFieldName     = $fieldConfig->getId()->getFieldName();
        $scope             = 'extend';

        $selfConfig   = $this->extendConfigProvider->getConfig($selfEntityClass);
        $targetConfig = $this->extendConfigProvider->getConfig($targetEntityClass);

        $selfRelationConfig = $this->findRelation($selfConfig, $fieldConfig, true);
    }

    /**
     * @param ConfigInterface $entityConfig
     * @param ConfigIdInterface $fieldConfigId
     * @param bool $target
     * @return null
     */
    protected function findRelation(ConfigInterface $entityConfig, ConfigIdInterface $fieldConfigId, $target = false)
    {
        $relations = $entityConfig->get('relation');
        foreach ($relations as &$relation) {
            if ($relation[$target ? 'target_field_id' : 'field_id'] == $fieldConfigId) {
                return $relation;
            }
        }

        return null;
    }
}
