<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * This class is used to fill the product's id after insert.
 * Since the id is not the identifier from the doctrine's point of view, we now need to fill the id.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LoadProductIdAfterCreationEventSubscriber implements EventSubscriber
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof ProductInterface
            || get_class($entity) === 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
            || null !== $entity->getId()
        ) {
            return;
        }

        $id = $this->connection->fetchOne(
            'SELECT id FROM pim_catalog_product WHERE uuid = ?',
            [$entity->getUuid()->getBytes()]
        );
        if (null !== $id) {
            $entity->setId((int) $id);
        }
    }
}
