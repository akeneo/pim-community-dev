<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitFreeTrialDbSchemaSubscriber implements EventSubscriberInterface
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        $query = <<<'SQL'
CREATE TABLE akeneo_free_trial_invited_user (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    status VARCHAR(15) NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->dbalConnection->executeQuery($query);
    }
}
