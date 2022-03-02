<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddUuidSubscriber implements EventSubscriber
{
    public function __construct(private Connection $connection)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postPersist];
    }

    public function postPersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if ($entity instanceof Product && $this->uuidColumnExists()) {
            $sql = <<<SQL
            UPDATE pim_catalog_product
            SET uuid = UUID_TO_BIN(:uuid)
            WHERE identifier = :identifier AND uuid IS NULL;
            SQL;

            $this->connection->executeQuery($sql, [
                'uuid' => Uuid::uuid4()->toString(),
                'identifier' => $entity->getIdentifier(),
            ]);
        }
    }

    private function uuidColumnExists(): bool
    {
        $rows = $this->connection->fetchAllAssociative("SHOW COLUMNS FROM pim_catalog_product LIKE 'uuid'");

        return \count($rows) >= 1;
    }
}
