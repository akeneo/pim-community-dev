<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Persistence;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MeasurementFamilyRepository implements MeasurementFamilyRepositoryInterface
{
    private Connection $sqlConnection;

    /** @var MeasurementFamily[] */
    private array $allMeasurementFamiliesCache = [];

    /** @var MeasurementFamily[] */
    private array $measurementFamilyCache = [];

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function all(): array
    {
        if ($this->allMeasurementFamiliesCache === []) {
            $this->allMeasurementFamiliesCache = $this->loadMeasurementFamiliesIndexByCodes();
        }

        return array_values($this->allMeasurementFamiliesCache);
    }

    public function getByCode(MeasurementFamilyCode $measurementFamilyCode): MeasurementFamily
    {
        $normalizedMeasurementFamilyCode = $measurementFamilyCode->normalize();
        if (!isset($this->measurementFamilyCache[$normalizedMeasurementFamilyCode])) {
            $this->measurementFamilyCache[$normalizedMeasurementFamilyCode] = $this->loadMeasurementFamily($measurementFamilyCode);
        }

        return $this->measurementFamilyCache[$normalizedMeasurementFamilyCode];
    }

    public function save(MeasurementFamily $measurementFamily)
    {
        $updateSql = <<<SQL
    INSERT INTO akeneo_measurement
        (code, labels, standard_unit, units)
    VALUES
        (:code, :labels, :standard_unit, :units)
    ON DUPLICATE KEY UPDATE
        labels = :labels,
        standard_unit = :standard_unit,
        units = :units;
SQL;
        $normalizedMeasurementFamily = $measurementFamily->normalize();

        $affectedRows = $this->sqlConnection->executeUpdate(
            $updateSql,
            [
                'code' => $normalizedMeasurementFamily['code'],
                'labels' => json_encode($normalizedMeasurementFamily['labels']),
                'standard_unit' => $normalizedMeasurementFamily['standard_unit_code'],
                'units' => json_encode($normalizedMeasurementFamily['units'])
            ]
        );

        // 0 if SAME, 1 if INSERT, 2 if UPDATE
        if ($affectedRows !== 0 && $affectedRows !== 1 && $affectedRows !== 2) {
            throw new \RuntimeException(
                sprintf('Expected to create/update one measurement family, but %d were affected', $affectedRows)
            );
        }

        $this->all();
        $this->allMeasurementFamiliesCache[$normalizedMeasurementFamily['code']] = $measurementFamily;
        $this->measurementFamilyCache[$normalizedMeasurementFamily['code']] = $measurementFamily;
    }

    public function countAllOthers(MeasurementFamilyCode $excludedMeasurementFamilyCode): int
    {
        $countQuery = <<<SQL
    SELECT COUNT(code)
    FROM akeneo_measurement
    WHERE code != :code;
SQL;

        $statement = $this->sqlConnection->executeQuery($countQuery, [
            'code' => $excludedMeasurementFamilyCode->normalize(),
        ]);

        return (int) $statement->fetch(\PDO::FETCH_COLUMN);
    }

    public function clear(): void
    {
        $this->allMeasurementFamiliesCache = [];
        $this->measurementFamilyCache = [];
    }

    public function deleteByCode(MeasurementFamilyCode $measurementFamilyCode)
    {
        $sql = <<<SQL
    DELETE FROM akeneo_measurement
    WHERE code = :code
SQL;

        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'code' => $measurementFamilyCode->normalize(),
            ]
        );

        if (1 !== $affectedRows) {
            throw new MeasurementFamilyNotFoundException();
        }

        if (isset($this->measurementFamilyCache[$measurementFamilyCode->normalize()])) {
            unset($this->measurementFamilyCache[$measurementFamilyCode->normalize()]);
        }
        if (isset($this->allMeasurementFamiliesCache[$measurementFamilyCode->normalize()])) {
            unset($this->allMeasurementFamiliesCache[$measurementFamilyCode->normalize()]);
        }
    }

    private function hydrateMeasurementFamily(
        string $code,
        string $normalizedLabels,
        string $standardUnit,
        string $normalizedUnits
    ): MeasurementFamily {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $code = Type::getType(Types::STRING)->convertToPhpValue($code, $platform);
        $labels = json_decode($normalizedLabels, true);
        $standardUnit = Type::getType(Types::STRING)->convertToPhpValue($standardUnit, $platform);
        //TODO check Type:JSON
        $units = array_map(fn (array $normalizedUnit) => $this->hydrateUnit(
            $normalizedUnit['code'],
            $normalizedUnit['labels'],
            $normalizedUnit['convert_from_standard'],
            $normalizedUnit['symbol']
        ), json_decode($normalizedUnits, true));

        return MeasurementFamily::create(
            MeasurementFamilyCode::fromString($code),
            LabelCollection::fromArray($labels),
            UnitCode::fromString($standardUnit),
            $units
        );
    }

    private function hydrateUnit(string $code, array $normalizedLabels, array $convertFromStandard, string $symbol)
    {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $code = Type::getType(Types::STRING)->convertToPhpValue($code, $platform);
        $operations = array_map(function (array $operation) use ($platform) {
            $operator = Type::getType(Types::STRING)->convertToPhpValue($operation['operator'], $platform);
            $value = Type::getType(Types::STRING)->convertToPhpValue($operation['value'], $platform);

            return Operation::create($operator, $value);
        }, $convertFromStandard);
        $symbol = Type::getType(Types::STRING)->convertToPhpValue($symbol, $platform);

        return Unit::create(
            UnitCode::fromString($code),
            LabelCollection::fromArray($normalizedLabels),
            $operations,
            $symbol
        );
    }

    /**
     * @return array
     *
     * @throws DBALException
     */
    private function loadMeasurementFamiliesIndexByCodes(): array
    {
        $selectAllQuery = <<<SQL
    SELECT
        code,
        labels,
        standard_unit,
        units
    FROM akeneo_measurement;
SQL;
        $statement = $this->sqlConnection->executeQuery($selectAllQuery);
        $results = $statement->fetchAllAssociative();

        $measurementFamiliesIndexByCodes = [];
        foreach ($results as $result) {
            $measurementFamiliesIndexByCodes[$result['code']] = $this->hydrateMeasurementFamily(
                $result['code'],
                $result['labels'],
                $result['standard_unit'],
                $result['units']
            );
        }

        return $measurementFamiliesIndexByCodes;
    }

    private function loadMeasurementFamily(MeasurementFamilyCode $measurementFamilyCode): ?MeasurementFamily
    {
        $sql = <<<SQL
    SELECT
        code,
        labels,
        standard_unit,
        units
    FROM akeneo_measurement
    WHERE `code` = :measurement_family_code;
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['measurement_family_code' => $measurementFamilyCode->normalize()]
        );
        $result = $statement->fetchAssociative();

        if (!$result) {
            throw new MeasurementFamilyNotFoundException();
        }

        return $this->hydrateMeasurementFamily(
            $result['code'],
            $result['labels'],
            $result['standard_unit'],
            $result['units']
        );
    }
}
