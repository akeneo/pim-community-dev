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

use Doctrine\DBAL\Connection;

class GetPublishedVersionIdsByVersionIdsQuery
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(array $versionIds): array
    {
        $query = <<<SQL
SELECT version_id FROM pimee_workflow_published_product WHERE version_id IN (:version_ids)
SQL;

        $results = $this->dbConnection->executeQuery(
            $query,
            ['version_ids' => $versionIds],
            ['version_ids' => Connection::PARAM_INT_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN);

        return array_map('intval', $results);
    }
}
