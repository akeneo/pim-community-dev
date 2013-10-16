<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata'
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /** @var OroEntityManager $em */
        $em = $event->getEntityManager();

        $configProvider = $em->getExtendManager()->getConfigProvider();
        $className      = $event->getClassMetadata()->getName();

        if ($configProvider->hasConfig($className)) {
            $config = $configProvider->getConfig($className);
            if ($config->is('is_extend')) {
                $cmBuilder = new ClassMetadataBuilder($event->getClassMetadata());

                if ($config->is('index')) {
                    foreach ($config->get('index') as $columnName => $enabled) {
                        $fieldConfig = $configProvider->getConfig($className, $columnName);

                        if ($enabled && $fieldConfig->is('state', ExtendManager::STATE_ACTIVE)) {
                            $cmBuilder->addIndex(
                                array(ExtendConfigDumper::PREFIX . $columnName),
                                'oro_idx_' . $columnName
                            );
                        }
                    }
                }

                if ($config->is('owner', ExtendManager::OWNER_SYSTEM)
                    && $config->is('relation')
                ) {
                    foreach ($config->get('relation') as $relation) {
                        /** @var FieldConfigId $fieldId */
                        $fieldId = $relation['field_id'];
                        /** @var FieldConfigId $targetFieldId */
                        $targetFieldId = $relation['target_field_id'];

                        switch ($fieldId->getFieldType()) {
                            case 'manyToOne':
                                $cmBuilder->addManyToOne(
                                    $fieldId->getFieldName(),
                                    $relation['target_entity'],
                                    $targetFieldId ? $targetFieldId->getFieldName() : null
                                );
                                break;
                            case 'oneToMany':
                                $cmBuilder->addOneToMany(
                                    $fieldId->getFieldName(),
                                    $relation['target_entity'],
                                    $targetFieldId->getFieldName()
                                );
                                break;
                            case 'manyToMany':
                                if ($relation['owner']) {
                                    $cmBuilder->addOwningManyToMany(
                                        $fieldId->getFieldName(),
                                        $relation['target_entity'],
                                        $targetFieldId ? $targetFieldId->getFieldName() : null
                                    );
                                } else {
                                    $cmBuilder->addInverseManyToMany(
                                        $targetFieldId->getFieldName(),
                                        $fieldId->getClassName(),
                                        $fieldId->getFieldName()
                                    );
                                }
                                break;
                        }

                    }
                }
            }

            //$em->getMetadataFactory()->getCacheDriver()->clear
        }
    }
}
