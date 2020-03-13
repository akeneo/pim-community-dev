<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Persistence;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetMeasurementFamilyCode implements GetMeasurementFamilyCodeInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forUnitCode(UnitCode $unitCode): MeasurementFamilyCode
    {
        $query = <<<SQL
SELECT code
FROM akeneo_measurement
WHERE 1 = JSON_CONTAINS(units, :unit_code_fragment, '$');
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['unit_code_fragment' => sprintf('{"code": "%s"}', $unitCode->normalize())]
        );
        $res = $stmt->fetchColumn();
        if (!$res) {
            throw new MeasurementFamilyNotFoundException();
        }
        $measurementFamilyCode = $this->connection->convertToPHPValue($res, Type::STRING);

        return MeasurementFamilyCode::fromString($measurementFamilyCode);
    }
}
