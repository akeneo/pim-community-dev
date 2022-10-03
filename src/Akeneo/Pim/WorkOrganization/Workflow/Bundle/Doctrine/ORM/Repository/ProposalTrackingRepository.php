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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProposalTracking;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\ProposalTrackingRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class ProposalTrackingRepository implements ProposalTrackingRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function save(ProposalTracking $proposalTracking): void
    {
        $this->sqlConnection->insert(
            'pimee_workflow_proposal_tracking',
            [
                'entity_type' => $proposalTracking->getEntityType(),
                'entity_id' => $proposalTracking->getEntityId(),
                'event_date' => $proposalTracking->getEventDate(),
                'payload' => $proposalTracking->getPayload(),
            ],
            [
                'entity_type' => \PDO::PARAM_STR,
                'entity_id' => \PDO::PARAM_INT,
                'event_date' => Types::DATETIME_MUTABLE,
                'payload' => Types::JSON,
            ]
        );
    }
}
