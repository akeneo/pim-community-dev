<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;

/**
 * Convert identifier into lazy entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTypeSubscriber implements EventSubscriber
{
    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

    /**
     * {@inheritdoc}
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $metadata = $args->getDocumentManager()->getClassMetadata(get_class($entity));
        foreach ($metadata->fieldMappings as $field => $mapping) {
            if ('entity' === $mapping['type']) {
                if (!isset($mapping['targetEntity'])) {
                    throw new \RuntimeException(
                        sprintf(
                            'Please provide the "targetEntity" of the %s::$%s field mapping',
                            $metadata->name,
                            $field
                        )
                    );
                }

                if (null !== $value = $metadata->reflFields[$field]->getValue($entity)) {
                    $metadata->reflFields[$field]->setValue(
                        $entity,
                        $this->entityManager->getReference($mapping['targetEntity'], $value)
                    );
                }
            }
        }
    }
}
