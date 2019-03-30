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
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectModelProposalIdsFromProductModelIdsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectModelProposalIdsFromProductModelIdsQuery implements SelectModelProposalIdsFromProductModelIdsQueryInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(array $productModelIds): array
    {
        $sql = <<<SQL
    SELECT id FROM pimee_workflow_product_model_draft
    WHERE product_model_id IN (:productModelIds)
    AND status = :status
SQL;

        $resultRows = $this->connection->executeQuery(
            $sql,
            ['productModelIds' => $productModelIds, 'status' => EntityWithValuesDraftInterface::READY],
            ['productModelIds' => Connection::PARAM_INT_ARRAY, 'status' => \PDO::PARAM_INT]
        )->fetchAll();

        return array_map(function ($rowData) {
            return (int) $rowData['id'];
        }, $resultRows);
    }
}
