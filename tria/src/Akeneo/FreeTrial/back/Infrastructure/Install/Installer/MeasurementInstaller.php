<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Doctrine\DBAL\Connection;

final class MeasurementInstaller implements FixtureInstaller
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function install(): void
    {
        $query = <<<SQL
INSERT IGNORE INTO akeneo_measurement (code, labels, standard_unit, units)
VALUES (
    'power_consumption',
    '{"en_US": "Power Consumption"}',
    'kw_h',
    '[{"code": "kw_h", "labels": {"en_US": "Kilowatts-hour"}, "symbol": "kWh", "convert_from_standard": [{"value": "1", "operator": "mul"}]}, {"code": "w_h", "labels": {"en_US": "Watts-hour"}, "symbol": "Wh", "convert_from_standard": [{"value": "1000", "operator": "div"}]}]'    
);
SQL;

        $this->dbConnection->executeQuery($query);
    }
}
