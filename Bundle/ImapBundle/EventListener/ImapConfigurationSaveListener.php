<?php

namespace Oro\Bundle\ImapBundle\EventListener;

use Doctrine\ORM\UnitOfWork;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\ImapBundle\Entity\ImapConfigurationOwnerInterface;

class ImapConfigurationSaveListener implements EventSubscriber
{
    const PROPERTY_NAME = 'imapConfiguration';

    /** @var array */
    protected $criticalFields = array(
        'host',
        'user'
    );

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush'
        );
    }

    /**
     * Check updated fields and if update affects critical fields
     * system should recreate configuration object and
     * move link to new one in owner entity
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ImapConfigurationOwnerInterface && $entity->getImapConfiguration()) {
                $changeset = $this->getConfigurationChangeSet($uow, $entity);

                if (array_intersect_key($changeset, array_flip($this->criticalFields))) {
                    $oldConfiguration = $entity->getImapConfiguration();
                    $newConfiguration = clone $oldConfiguration;

                    $em->refresh($oldConfiguration);
                    $oldConfiguration->setIsActive(false);
                    $em->persist($oldConfiguration);

                    $em->persist($newConfiguration);

                    $entity->setImapConfiguration($newConfiguration);
                    $uow->computeChangeSets();
                }
            }
        }
    }

    /**
     * Returns array of changed field
     * Empty array will be returned in case when configuration was not changed or just created
     *
     * @param UnitOfWork                      $uow
     * @param ImapConfigurationOwnerInterface $entity
     *
     * @return array
     */
    protected function getConfigurationChangeSet(UnitOfWork $uow, ImapConfigurationOwnerInterface $entity)
    {
        $changes = $uow->getEntityChangeSet($entity);
        if (isset($changes[self::PROPERTY_NAME]) && $changes[self::PROPERTY_NAME][0] === null) {
            // case when configuration entity is newly created
            return array();
        }

        return $uow->getEntityChangeSet($entity->getImapConfiguration());
    }
}
