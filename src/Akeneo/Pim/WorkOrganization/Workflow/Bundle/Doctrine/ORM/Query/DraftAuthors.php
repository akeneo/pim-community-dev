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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\DraftAuthors as DraftAuthorsInterface;
use Doctrine\DBAL\Connection;

/**
 * Find all authors for all drafts (product & product model)
 */
class DraftAuthors implements DraftAuthorsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findAuthors(?string $search, int $page = 1, int $limit = 20, array $identifiers = []): array
    {
        $sql = <<<SQL
(
    SELECT DISTINCT author AS username, author_label AS label
    FROM pimee_workflow_product_model_draft %s
)
UNION 
(
    SELECT DISTINCT author AS username, author_label AS label
    FROM pimee_workflow_product_draft %s
) 
LIMIT :start,:limit
SQL;
        $sqlSearch = ' WHERE 1=1';

        if (null !== $search && '' !== $search) {
            $sqlSearch .= ' AND (author LIKE :search OR author_label LIKE :search)';
        }

        if (!empty($identifiers)) {
            $sqlSearch .= ' AND author in (:identifiers)';
        }

        $queryParams = [
            'start' => $limit * ($page - 1),
            'limit' => $limit,
        ];
        $queryParamTypes = [
            'start' => \PDO::PARAM_INT,
            'limit' => \PDO::PARAM_INT,
        ];

        if (null !== $search && '' !== $search) {
            $queryParams['search'] = "%" . $search . "%";
            $queryParamTypes['search'] = \PDO::PARAM_STR;
        }

        if (!empty($identifiers)) {
            $queryParams['identifiers'] = $identifiers;
            $queryParamTypes['identifiers'] = Connection::PARAM_STR_ARRAY;
        }

        $stmt = $this->connection->executeQuery(
            sprintf($sql, $sqlSearch, $sqlSearch),
            $queryParams,
            $queryParamTypes,
        );

        return $stmt->fetchAll();
    }
}
