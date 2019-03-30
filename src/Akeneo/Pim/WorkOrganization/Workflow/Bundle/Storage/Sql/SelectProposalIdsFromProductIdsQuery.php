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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProposalIdsFromProductIdsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectProposalIdsFromProductIdsQuery implements SelectProposalIdsFromProductIdsQueryInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(array $productIds): array
    {
        $sql = <<<SQL
    SELECT id FROM pimee_workflow_product_draft
    WHERE product_id IN (:productIds)
    AND status = :status
SQL;

        $resultRows = $this->connection->executeQuery(
            $sql,
            ['productIds' => $productIds, 'status' => EntityWithValuesDraftInterface::READY],
            ['productIds' => Connection::PARAM_INT_ARRAY, 'status' => \PDO::PARAM_INT]
        )->fetchAll();

        return array_map(function ($rowData) {
            return (int) $rowData['id'];
        }, $resultRows);
    }
}
