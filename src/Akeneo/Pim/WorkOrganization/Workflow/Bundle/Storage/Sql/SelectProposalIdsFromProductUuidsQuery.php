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
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProposalIdsFromProductUuidsQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectProposalIdsFromProductUuidsQuery implements SelectProposalIdsFromProductUuidsQueryInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(array $productUuids): array
    {
        if ([] === $productUuids) {
            return [];
        }

        $sql = <<<SQL
    SELECT id FROM pimee_workflow_product_draft
    WHERE product_uuid IN (:productUuids)
    AND status = :status
SQL;

        $productUuidsAsBytes = \array_map(static fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        return $this->connection->executeQuery(
            $sql,
            ['productUuids' => $productUuidsAsBytes, 'status' => EntityWithValuesDraftInterface::READY],
            ['productUuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        return array_map(function ($rowData) {
            return (int) $rowData['id'];
        }, $resultRows);
    }
}
