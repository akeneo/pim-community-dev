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

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich;

use Doctrine\DBAL\Connection;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlFindAllExistentRecordsForReferenceEntityIdentifiers
{
    private const BATCH_SIZE = 200;

    public function __construct(
        private Connection $connection
    ) {
    }

    public function forReferenceEntityIdentifiersAndRecordCodes(array $referenceEntityIdentifiersToCodes): array
    {
        $queryParams = [];
        foreach ($referenceEntityIdentifiersToCodes as $referenceEntityIdentifier => $recordCodes) {
            foreach ($recordCodes as $recordCode) {
                $queryParams[] = $referenceEntityIdentifier;
                $queryParams[] = $recordCode;
            }
        }

        $chunkedQueryParams = \array_chunk($queryParams, 2 * self::BATCH_SIZE);
        $results = [];
        foreach ($chunkedQueryParams as $queryParamsChunk) {
            $batchedResults = $this->batchedQuery($queryParamsChunk);
            $results = \array_merge_recursive($results, $batchedResults);
        }

        return $results;
    }

    /**
     * @param string[] $queryParams
     * @return array<string, string[]>
     */
    private function batchedQuery(array $queryParams): array
    {
        if ([] === $queryParams) {
            return [];
        }

        $query = <<<SQL
        SELECT /*+ SET_VAR( range_optimizer_max_mem_size = 50000000) */
            reference_entity_identifier as reference_entity_identifier,
            JSON_ARRAYAGG(code) as record_code
        FROM akeneo_reference_entity_record
        WHERE (reference_entity_identifier, code) IN (%s)
        GROUP BY reference_entity_identifier;
        SQL;

        /**
         * We have to build the query by hand because Doctrine does not support tuple for IN (:myParameter) things
         * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/data-retrieval-and-manipulation.html#list-of-parameters-conversion
         */
        $queryStringParams = \array_fill(0, \count($queryParams) / 2, '(?, ?)');

        $rawResults = $this->connection->executeQuery(
            \sprintf($query, \implode(',', $queryStringParams)),
            $queryParams
        )->fetchAllAssociative();

        return \array_reduce($rawResults, static function (array $results, array $item) {
            $results[\strtolower($item['reference_entity_identifier'])] = \json_decode($item['record_code'], true);

            return $results;
        }, []);
    }
}
