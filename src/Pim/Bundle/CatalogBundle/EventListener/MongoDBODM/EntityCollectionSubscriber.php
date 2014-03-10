<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory;

/**
 * Convert identifiers collection into lazy entity collection
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityCollectionSubscriber implements EventSubscriber
{
    protected $factory;

    public function __construct(ReferencedCollectionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $metadata = $args->getDocumentManager()->getClassMetadata(get_class($entity));
        foreach ($metadata->fieldMappings as $field => $mapping) {
            if ('entities' === $mapping['type']) {
                if (!isset($mapping['targetEntity'])) {
                    throw new \RuntimeException(
                        sprintf(
                            'Please provide the "targetEntity" of the %s::$%s field mapping',
                            $metadata->name,
                            $field
                        )
                    );
                }

                $metadata->reflFields[$field]->setValue(
                    $entity,
                    $this->factory->create($mapping['targetEntity'], $metadata->reflFields[$field]->getValue($entity))
                );
            }
        }
    }
}
