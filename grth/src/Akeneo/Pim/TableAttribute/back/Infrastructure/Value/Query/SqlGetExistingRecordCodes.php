<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Query;

use Doctrine\DBAL\Connection;

class SqlGetExistingRecordCodes implements GetExistingRecordCodes
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function fromReferenceEntityIdentifierAndRecordCodes(array $recordCodes): array
    {
        $queryParams = [];
        $queryStringParams = [];

        foreach ($recordCodes as $referenceEntityIdentifier => $records) {
            foreach ($records as $recordCode) {
                $queryParams[] = $referenceEntityIdentifier;
                $queryParams[] = $recordCode;
                $queryStringParams[] = "(?, ?)";
            }
        }

        if (empty($queryParams) || empty($queryStringParams)) {
            return [];
        }

        $query = <<<SQL
            SELECT reference_entity_identifier as reference_entity_identifier, JSON_ARRAYAGG(code) as record_code
            FROM akeneo_reference_entity_record
            WHERE (reference_entity_identifier, code) IN (%s)
            GROUP BY reference_entity_identifier
            SQL;

        $rawResults = $this->connection->executeQuery(
            sprintf($query, implode(',', $queryStringParams)),
            $queryParams
        )->fetchAllAssociative();

        return array_reduce($rawResults, static function (array $results, array $item) {
            $results[\strtolower($item['reference_entity_identifier'])] = json_decode($item['record_code'], true);
            return $results;
        }, []);
    }
}
