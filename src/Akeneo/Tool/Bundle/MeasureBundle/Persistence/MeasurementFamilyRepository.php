<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\Persistence;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamilyRepository implements MeasurementFamilyRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(
        Connection $sqlConnection
    ) {
        $this->sqlConnection = $sqlConnection;
    }

    public function all(): \Iterator
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
        $results = $statement->fetchAll();

        foreach ($results as $result) {
            yield $this->hydrateMeasurementFamily(
                $result['code'],
                $result['labels'],
                $result['standard_unit'],
                $result['units']
            );
        }
    }

    private function hydrateMeasurementFamily(
        string $code,
        string $normalizedLabels,
        string $standardUnit,
        string $normalizedUnits
    ): MeasurementFamily {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $code = Type::getType(Type::STRING)->convertToPhpValue($code, $platform);
        $labels = json_decode($normalizedLabels, true);
        $standardUnit = Type::getType(Type::STRING)->convertToPhpValue($standardUnit, $platform);
        //TODO check Type:JSON
        $units = array_map(function (array $normalizedUnit) {
            return $this->hydrateUnit(
                $normalizedUnit['code'],
                $normalizedUnit['labels'],
                $normalizedUnit['convert'],
                $normalizedUnit['symbol']
            );
        }, json_decode($normalizedUnits, true));

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

        $code = Type::getType(Type::STRING)->convertToPhpValue($code, $platform);
        $operations = array_map(function (array $operation) use ($platform) {
            $operator = Type::getType(Type::STRING)->convertToPhpValue($operation['operator'], $platform);
            $value = Type::getType(Type::STRING)->convertToPhpValue($operation['value'], $platform);

            return Operation::create($operator, $value);
        }, $convertFromStandard);
        $symbol = Type::getType(Type::STRING)->convertToPhpValue($symbol, $platform);

        return Unit::create(
            UnitCode::fromString($code),
            LabelCollection::fromArray($normalizedLabels),
            $operations,
            $symbol
        );
    }
}
