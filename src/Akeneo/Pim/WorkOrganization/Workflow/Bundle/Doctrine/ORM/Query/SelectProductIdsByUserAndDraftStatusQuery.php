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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductIdsByUserAndDraftStatusQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectProductIdsByUserAndDraftStatusQuery implements SelectProductIdsByUserAndDraftStatusQueryInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $username, array $draftStatuses)
    {
        $querySql = <<<SQL
    SELECT product_id FROM pimee_workflow_product_draft
    WHERE author = :author
    AND status IN (:statuses)
SQL;

        $stmt = $this->connection->executeQuery(
            $querySql,
            ['author' => $username, 'statuses' => $draftStatuses],
            ['author' => \PDO::PARAM_STR, 'statuses' => Connection::PARAM_INT_ARRAY]
        );
        $resultRows = $stmt->fetchAll();

        $productIds = [];
        foreach ($resultRows as $resultRow) {
            $productIds[] = (int) $resultRow['product_id'];
        }

        return $productIds;
    }
}
