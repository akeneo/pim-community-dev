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

                        if ($enabled && !$fieldConfig->is('state', ExtendManager::STATE_NEW)) {
                            $cmBuilder->addIndex(
                                array(ExtendConfigDumper::PREFIX . $columnName),
                                'oro_idx_' . $columnName
                            );
                        }
                    }
                }

                if ($config->is('relation')) {
                    foreach ($config->get('relation') as $relation) {
                        if ($relation['assign'] && $fieldId = $relation['field_id']) {
                            /** @var FieldConfigId $targetFieldId */
                            $targetFieldId = $relation['target_field_id'];

                            $targetFieldName = $targetFieldId
                                ? ExtendConfigDumper::PREFIX . $targetFieldId->getFieldName()
                                : null;

                            $fieldName = ExtendConfigDumper::PREFIX . $fieldId->getFieldName();

                            switch ($fieldId->getFieldType()) {
                                case 'manyToOne':
                                    $cmBuilder->addManyToOne(
                                        $fieldName,
                                        $relation['target_entity'],
                                        $targetFieldName
                                    );
                                    break;
                                case 'oneToMany':
                                    $cmBuilder->addOneToMany(
                                        $fieldName,
                                        $relation['target_entity'],
                                        $targetFieldName
                                    );
                                    break;
                                case 'manyToMany':
                                    if ($relation['owner']) {
                                        $cmBuilder->addOwningManyToMany(
                                            $fieldName,
                                            $relation['target_entity'],
                                            $targetFieldName
                                        );
                                    } else {
                                        $cmBuilder->addInverseManyToMany(
                                            $targetFieldName,
                                            $fieldId->getClassName(),
                                            $fieldName
                                        );
                                    }
                                    break;
                            }
                        }
                    }
                }
            }

            $em->getMetadataFactory()->setMetadataFor($className, $event->getClassMetadata());
        }
    }
}
