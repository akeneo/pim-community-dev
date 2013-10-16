<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Event\NewFieldConfigModelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
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
            $entityConfig = $event->getConfigManager()
                ->getProvider($scope)
                ->getConfig($className);

            if ($event->getConfig()->get('state') != ExtendManager::STATE_NEW
                && $event->getConfig()->get('state') != ExtendManager::STATE_DELETED
                && !isset($change['state'])
            ) {
                $event->getConfig()->set('state', ExtendManager::STATE_UPDATED);
                $event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
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

        $fieldConfig = $configProvider->getConfig($event->getClassName(), $event->getFieldName());
        if (in_array($event->getFieldType(), array('oneToMany', 'manyToOne', 'manyToMany'))
            && $fieldConfig->is('is_inverse', false)
            && $fieldConfig->is('target_entity')
        ) {

            $classArray = explode('\\', $fieldConfig->getId()->getClassName());
            $relationName  = strtolower(array_pop($classArray)) . '_' . $event->getFieldName();

            switch ($event->getFieldType()) {
                case 'oneToMany':
                    $type = 'manyToOne';
                    $fieldConfig->set('mappedBy', $relationName);
                    break;
                case 'manyToOne':
                    $type = 'oneToMany';
                    break;
                case 'manyToMany':
                    $type = 'manyToMany';
                    break;
                default:
                    $type = '';
            }

            $relationConfig = array(
                'type' => $type,
                'options' => array(
                    'is_inverse' => true,
                    'target_entity' => $event->getClassName(),
                 )
            );

            $this->extendManager->createField(
                $fieldConfig->get('target_entity'),
                $relationName,
                $relationConfig
            );
        }
    }
}
