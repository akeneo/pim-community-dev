<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Storage\Sql;

use Doctrine\DBAL\Connection;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Query\CountProductModelProposals as CountProductModelProposalsQuery;

class CountProductModelProposals implements CountProductModelProposalsQuery
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): int
    {
        $proposalStatus = EntityWithValuesDraftInterface::READY;

        $sql = <<<SQL
        SELECT COUNT(draft.id) AS total
        FROM pimee_workflow_product_model_draft draft
        WHERE draft.status = $proposalStatus
SQL;

        $count = $this->connection->query($sql)->fetchColumn(0);

        return (int) $count;
    }
}
