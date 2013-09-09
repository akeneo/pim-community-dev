<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigIdInterface;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConfigProvider
     */
    protected $extendConfigProvider;

    /**
     * @param ConfigProvider $extendConfigProvider
     */
    public function __construct(ConfigProvider $extendConfigProvider)
    {
        $this->extendConfigProvider = $extendConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_PERSIST_CONFIG => 'persistConfig',
            Events::PRE_PERSIST_CONFIG => 'persistConfig',
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
            && $event->getConfig()->getId() instanceof FieldConfigIdInterface
            && $event->getConfig()->is('is_extend')
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
            && $event->getConfig()->getId() instanceof FieldConfigIdInterface
            && !in_array($event->getConfig()->getId()->getFieldType(), array('text'))
            && isset($change['is_visible'])

        ) {
            /** @var ConfigProvider $extendConfigProvider */
            $extendConfigProvider = $event->getConfigManager()->getProvider('extend');

            $fieldName = $event->getConfig()->getId()->getFieldName();

            $extendConfig = $extendConfigProvider->getConfig($className, $fieldName);
            $extendConfig->set('is_indexable', $event->getConfig()->get('is_visible'));

            $event->getConfigManager()->persist($extendConfig);
        }
    }
}
