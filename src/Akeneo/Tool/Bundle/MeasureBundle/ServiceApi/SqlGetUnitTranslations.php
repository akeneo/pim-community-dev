<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\ServiceApi;

use Doctrine\DBAL\Connection;

class SqlGetUnitTranslations implements GetUnitTranslations
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byMeasurementFamilyCodeAndLocale(string $measurementFamilyCode, string $localeCode): array
    {
        $sql = <<<SQL
SELECT unit_labels.*
FROM akeneo_measurement am, JSON_TABLE(am.units,
    '$[*]' COLUMNS(
        code VARCHAR(100) PATH '$.code',
        label VARCHAR(100) PATH :labelPath
    )
) AS unit_labels
WHERE am.code = :measurementFamilyCode;
SQL;

        return $this->connection->executeQuery(
            $sql,
            [
                'labelPath' => sprintf('$.labels.%s', $localeCode),
                'measurementFamilyCode' => $measurementFamilyCode
            ]
        )->fetchAllKeyValue();
    }
}
