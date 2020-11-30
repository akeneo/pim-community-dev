<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindDraftIdsConcerningRemovedAttributesQueryInterface;
use Doctrine\DBAL\Connection;

final class FindDraftIdsConcerningRemovedAttributesQuery implements FindDraftIdsConcerningRemovedAttributesQueryInterface
{
    /** @var Connection */
    private $connection;

    /** @var int */
    private $batchSize;

    public function __construct(
        Connection $connection,
        int $batchSize
    ) {
        $this->connection = $connection;
        $this->batchSize = $batchSize;
    }

    public function forProducts(): \Iterator
    {
        $searchAfterId = 0;

        $sql = <<<SQL
SELECT DISTINCT pd.id AS product_draft_id
FROM `pimee_workflow_product_draft` pd,
JSON_TABLE(JSON_KEYS(JSON_EXTRACT(pd.changes, '$.values')), '$[*]' COLUMNS (
    `code` VARCHAR(100) PATH '$' 
)) pd_attribute_codes
LEFT OUTER JOIN `pim_catalog_attribute` attr ON (pd_attribute_codes.code = attr.code COLLATE utf8mb4_general_ci)
WHERE pd.id > :search_after_id
AND attr.id IS NULL
ORDER BY pd.id
LIMIT :limit;
SQL;

        while (true) {
            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'search_after_id' => $searchAfterId,
                    'limit' => $this->batchSize
                ],
                [
                    'search_after_id' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT
                ]
            )->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($rows)) {
                return;
            }

            $searchAfterId = end($rows);
            reset($rows);

            yield $rows;
        }
    }

    public function forProductModels(): \Iterator
    {
        $searchAfterId = 0;

        $sql = <<<SQL
SELECT DISTINCT pmd.id AS product_model_draft_id
FROM `pimee_workflow_product_model_draft` pmd,
JSON_TABLE(JSON_KEYS(JSON_EXTRACT(pmd.changes, '$.values')), '$[*]' COLUMNS (
    `code` VARCHAR(100) PATH '$' 
)) pmd_attribute_codes
LEFT OUTER JOIN `pim_catalog_attribute` attr ON (pmd_attribute_codes.code = attr.code COLLATE utf8mb4_general_ci)
WHERE pmd.id > :search_after_id
AND attr.id IS NULL
ORDER BY pmd.id
LIMIT :limit;
SQL;

        while (true) {
            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'search_after_id' => $searchAfterId,
                    'limit' => $this->batchSize
                ],
                [
                    'search_after_id' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT
                ]
            )->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($rows)) {
                return;
            }

            $searchAfterId = end($rows);
            reset($rows);

            yield $rows;
        }
    }
}
