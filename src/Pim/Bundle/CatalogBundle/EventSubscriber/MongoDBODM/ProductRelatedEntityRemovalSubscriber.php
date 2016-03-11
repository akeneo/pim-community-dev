<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Console\CommandLauncher;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;

/**
 * Updates product document when an entity related to product is removed
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRelatedEntityRemovalSubscriber implements EventSubscriber
{
    /** @var CommandLauncher */
    protected $launcher;

    /** @var string */
    protected $logFile;

    /**
     * @param CommandLauncher $launcher
     * @param string          $logFile
     */
    public function __construct(CommandLauncher $launcher, $logFile)
    {
        $this->launcher = $launcher;
        $this->logFile = $logFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::onFlush];
    }

    /**
     * Launches a command to update all products by giving the entity type and ID.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $unitOfWork     = $args->getEntityManager()->getUnitOfWork();
        $entities       = $unitOfWork->getScheduledEntityDeletions();
        $pendingUpdates = $this->getPendingUpdates($entities);

        if (null !== $pendingUpdates && $pendingUpdates['entityName'] && !empty($pendingUpdates['ids'])) {
            $command = sprintf(
                'pim:product:remove-related-entity %s %s',
                $pendingUpdates['entityName'],
                implode(',', $pendingUpdates['ids'])
            );

            $this->launcher->executeBackground($command, $this->logFile);
        }
    }

    /**
     * Returns the list of entity IDs that need to be removed from
     * products and the type of the entity.
     * Returned array is organized as follow:
     *
     * [
     *     'entityName' => 'EntityName',
     *     'ids'        => [1, 4, 27, 31, 42]
     * ]
     *
     * @param object[] $entities
     *
     * @return null|array
     */
    protected function getPendingUpdates(array $entities)
    {
        if (empty($entities)) {
            return null;
        }

        $pendingUpdates = [
            'entityName' => '',
            'ids'        => [],
        ];

        foreach ($entities as $entity) {
            $entityName = $this->getEntityName($entity);

            if (null !== $entityName) {
                if ('' === $pendingUpdates['entityName']) {
                    $pendingUpdates['entityName'] = $entityName;
                }

                if ($pendingUpdates['entityName'] !== $entityName) {
                    throw new \InvalidArgumentException(sprintf(
                        'You can delete only one type of entity at a time, but you tried to delete both %s and %s',
                        $pendingUpdates['entityName'],
                        $entityName
                    ));
                }

                $pendingUpdates['ids'] = array_merge(
                    $pendingUpdates['ids'],
                    [$entity->getId()]
                );
            }
        }

        return $pendingUpdates;
    }

    /**
     * @param object $entity
     *
     * @return null|string
     */
    protected function getEntityName($entity)
    {
        if ($entity instanceof AssociationTypeInterface) {
            return 'AssociationType';
        }

        if ($entity instanceof AttributeInterface) {
            return 'Attribute';
        }

        if ($entity instanceof AttributeOptionInterface) {
            return 'AttributeOption';
        }

        if ($entity instanceof CategoryInterface) {
            return 'Category';
        }

        if ($entity instanceof FamilyInterface) {
            return 'Family';
        }

        if ($entity instanceof GroupInterface) {
            return 'Group';
        }

        if ($entity instanceof ChannelInterface) {
            return 'Channel';
        }

        return null;
    }
}
