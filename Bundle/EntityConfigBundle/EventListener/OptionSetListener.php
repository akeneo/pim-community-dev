<?php

namespace Oro\Bundle\EntityConfigBundle\EventListener;

use Doctrine\Common\Inflector\Inflector;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use Oro\Bundle\EntityConfigBundle\Config\Config;

use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSetRelation;

/**
 * Class OptionSetListener
 * @package Oro\Bundle\EntityConfigBundle\EventListener
 *
 * - needed by entity extend bundle functionality
 * - listen to doctrine PostPersist event
 * - determinate if NEW optionSet field type model have been created (field create action)
 * - persists and flush option relations for created OptionSet
 */
class OptionSetListener
{
    protected $needFlush = false;

    public function postPersist(LifecycleEventArgs $event)
    {
        /** @var OroEntityManager $em */
        $em             = $event->getEntityManager();
        $entity         = $event->getEntity();
        $configProvider = $em->getExtendManager()->getConfigProvider();

        $className = get_class($entity);
        if ($configProvider->hasConfig($className)) {
            $config = $configProvider->getConfig($className);
            $schema = $config->get('schema');
            if (isset($schema['relation'])) {
                foreach ($schema['relation'] as $fieldName) {
                    /** @var Config $fieldConfig */
                    $fieldConfig = $configProvider->getConfig($className, $fieldName);
                    if ($fieldConfig->getId()->getFieldType() == 'optionSet'
                        && $setData = $entity->{Inflector::camelize('get_' . $fieldName)}()
                    ) {
                        $model = $configProvider->getConfigManager()->getConfigFieldModel(
                            $fieldConfig->getId()->getClassName(),
                            $fieldConfig->getId()->getFieldName()
                        );

                        /**
                         * in case of single select field type, should wrap value in array
                         */
                        if ($setData && !is_array($setData)) {
                            $setData = [$setData];
                        }

                        foreach ($setData as $option) {
                            $optionSetRelation = new OptionSetRelation();
                            $optionSetRelation->setData(
                                null,
                                $entity->getId(),
                                $model,
                                $em->getRepository(OptionSet::ENTITY_NAME)->find($option)
                            );

                            $em->persist($optionSetRelation);
                            $this->needFlush = true;
                        }
                    }
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if ($this->needFlush) {
            $this->needFlush = false;
            $eventArgs->getEntityManager()->flush();
        }
    }
}
