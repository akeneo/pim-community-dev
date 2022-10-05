<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProposalTracking;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstallDatabaseSubscriber implements EventSubscriberInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'createProposalTrackingTable',
        ];
    }

    public function createProposalTrackingTable(): void
    {
        $sql = <<<SQL
DROP TABLE IF EXISTS pimee_workflow_proposal_tracking;
CREATE TABLE pimee_workflow_proposal_tracking (
    entity_type VARCHAR(64) NOT NULL,
    entity_id INT NOT NULL,
    event_date datetime NOT NULL,
    payload JSON NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

        $this->sqlConnection->exec($sql);
    }
}
