<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\PublicApi;

use Doctrine\DBAL\Connection;

class SqlGetUnit implements GetUnit
{
    public function __construct(private Connection $connection)
    {
    }

    public function byMeasurementFamilyCodeAndUnitCode(string $measurementFamilyCode, string $unitCode): array
    {
        $sql = <<<SQL
SELECT unit.*
FROM akeneo_measurement measurement, JSON_TABLE(measurement.units,
    '$[*]' COLUMNS(
        code VARCHAR(100) PATH '$.code',
        label VARCHAR(100) PATH '$.labels',
        symbol VARCHAR(100) PATH '$.symbol',
        convert_from_standard VARCHAR(100) PATH '$.convert_from_standard'
    )
) AS unit
WHERE measurement.code = :measurementFamilyCode
AND unit.code = :unitCode;
SQL;

        return $this->connection->executeQuery(
            $sql,
            [
                'unitCode' => $unitCode,
                'measurementFamilyCode' => $measurementFamilyCode
            ]
        )->fetchKeyValue();
    }
}
