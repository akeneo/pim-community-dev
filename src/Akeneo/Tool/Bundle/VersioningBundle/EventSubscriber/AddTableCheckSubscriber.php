<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddTableCheckSubscriber implements EventSubscriberInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'addTableCheck',
        ];
    }

    public function addTableCheck(): void
    {
        $this->connection->executeQuery(<<<SQL
        ALTER TABLE pim_versioning_version 
            ADD CONSTRAINT resource_id_uuid_check CHECK (
                (resource_id IS NULL AND resource_uuid IS NOT NULL) OR (resource_id IS NOT NULL AND resource_uuid IS NULL)
            );
        SQL);
    }
}
