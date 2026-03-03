<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\ServiceApi;

use Doctrine\DBAL\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SqlGetUnit implements GetUnit
{
    public function __construct(private Connection $connection)
    {
    }

    public function byMeasurementFamilyCodeAndUnitCode(string $measurementFamilyCode, string $unitCode): Unit
    {
        $sql = <<<SQL
SELECT unit.*
FROM akeneo_measurement measurement, JSON_TABLE(measurement.units,
    '$[*]' COLUMNS(
        code VARCHAR(100) PATH '$.code',
        labels JSON PATH '$.labels',
        symbol VARCHAR(100) PATH '$.symbol',
        convert_from_standard JSON PATH '$.convert_from_standard'
    )
) AS unit
WHERE measurement.code = :measurementFamilyCode
AND unit.code = :unitCode;
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            [
                'unitCode' => $unitCode,
                'measurementFamilyCode' => $measurementFamilyCode
            ]
        )->fetchAssociative();

        if (!$result) {
            throw new \Exception(sprintf(
                'Unit code %s with family code %s was not found',
                $unitCode,
                $measurementFamilyCode
            ));
        }

        $unit = new Unit();
        $unit->code = $result['code'];
        $unit->labels = json_decode($result['labels'], true);
        $unit->symbol = $result['symbol'];
        $unit->convertFromStandard = json_decode($result['convert_from_standard'], true);

        return $unit;
    }
}
