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
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forReferenceEntityIdentifiersAndRecordCodes(array $referenceEntityIdentifiersToCodes): array
    {
        if (empty($referenceEntityIdentifiersToCodes)) {
            return [];
        }

        /**
         * We have to build the query by hand because Doctrine does not support tuple for IN (:myParameter) things
         * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/data-retrieval-and-manipulation.html#list-of-parameters-conversion
         */

        $queryParams = [];
        $queryStringParams = [];

        foreach ($referenceEntityIdentifiersToCodes as $referenceEntityIdentifier => $recordCodes) {
            foreach ($recordCodes as $recordCode) {
                $queryParams[] = $referenceEntityIdentifier;
                $queryParams[] = $recordCode;
                $queryStringParams[] = "(?, ?)";
            }
        }

        $query = <<<SQL
SELECT reference_entity_identifier as reference_entity_identifier, JSON_ARRAYAGG(code) as record_code
FROM akeneo_reference_entity_record
WHERE (reference_entity_identifier, code) IN (%s)
GROUP BY reference_entity_identifier;
SQL;

        $rawResults = $this->connection->executeQuery(
            sprintf($query, implode(',', $queryStringParams)),
            $queryParams
        )->fetchAll();

        return array_reduce($rawResults, function (array $results, array $item) {
            $results[$item['reference_entity_identifier']] = json_decode($item['record_code'], true);

            return $results;
        }, []);
    }
}
