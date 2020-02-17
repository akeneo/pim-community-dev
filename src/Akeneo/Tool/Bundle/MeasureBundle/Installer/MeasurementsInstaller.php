<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {description}
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementsInstaller implements EventSubscriberInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createSchema'],
            InstallerEvents::POST_LOAD_FIXTURES => ['loadFixtures'],
        ];
    }

    public function createSchema(): void
    {
        $sql = <<<SQL
CREATE TABLE `akeneo_measurement` (
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `standard_unit` varchar(100) NOT NULL COMMENT '(DC2Type:datetime)',
  `units` JSON NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
SQL;

        $this->connection->exec($sql);
    }

    public function loadFixtures(InstallerEvent $event): void
    {
        $sql = <<<SQL
INSERT INTO `akeneo_measurement` (`code`, `standard_unit`, `units`)
VALUES
    ('Area', 'SQUARE_METER', '[{"code":"SQUARE_MILLIMETER","convert":[{"operator":0,"value":{"mul":"0.000001"}}],"symbol":"mm²"},{"code":"SQUARE_CENTIMETER","convert":[{"operator":0,"value":{"mul":"0.0001"}}],"symbol":"cm²"},{"code":"SQUARE_DECIMETER","convert":[{"operator":0,"value":{"mul":"0.01"}}],"symbol":"dm²"},{"code":"SQUARE_METER","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"m²"},{"code":"CENTIARE","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"ca"},{"code":"SQUARE_DEKAMETER","convert":[{"operator":0,"value":{"mul":"100"}}],"symbol":"dam²"},{"code":"ARE","convert":[{"operator":0,"value":{"mul":"100"}}],"symbol":"a"},{"code":"SQUARE_HECTOMETER","convert":[{"operator":0,"value":{"mul":"10000"}}],"symbol":"hm²"},{"code":"HECTARE","convert":[{"operator":0,"value":{"mul":"10000"}}],"symbol":"ha"},{"code":"SQUARE_KILOMETER","convert":[{"operator":0,"value":{"mul":"1000000"}}],"symbol":"km²"},{"code":"SQUARE_MIL","convert":[{"operator":0,"value":{"mul":"0.00000000064516"}}],"symbol":"sq mil"},{"code":"SQUARE_INCH","convert":[{"operator":0,"value":{"mul":"0.00064516"}}],"symbol":"in²"},{"code":"SQUARE_FOOT","convert":[{"operator":0,"value":{"mul":"0.09290304"}}],"symbol":"ft²"},{"code":"SQUARE_YARD","convert":[{"operator":0,"value":{"mul":"0.83612736"}}],"symbol":"yd²"},{"code":"ARPENT","convert":[{"operator":0,"value":{"mul":"3418.89"}}],"symbol":"arpent"},{"code":"ACRE","convert":[{"operator":0,"value":{"mul":"4046.856422"}}],"symbol":"A"},{"code":"SQUARE_FURLONG","convert":[{"operator":0,"value":{"mul":"40468.726"}}],"symbol":"fur²"},{"code":"SQUARE_MILE","convert":[{"operator":0,"value":{"mul":"2589988.110336"}}],"symbol":"mi²"}]'),
    ('Binary', 'BYTE', '[{"code":"BIT","convert":[{"operator":0,"value":{"mul":"0.125"}}],"symbol":"b"},{"code":"BYTE","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"B"},{"code":"KILOBYTE","convert":[{"operator":0,"value":{"mul":"1024"}}],"symbol":"kB"},{"code":"MEGABYTE","convert":[{"operator":0,"value":{"mul":"1048576"}}],"symbol":"MB"},{"code":"GIGABYTE","convert":[{"operator":0,"value":{"mul":"1073741824"}}],"symbol":"GB"},{"code":"TERABYTE","convert":[{"operator":0,"value":{"mul":"1099511627776"}}],"symbol":"TB"}]'),
    ('Decibel', 'DECIBEL', '[{"code":"DECIBEL","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"dB"}]'),
    ('Frequency', 'HERTZ', '[{"code":"HERTZ","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"Hz"},{"code":"KILOHERTZ","convert":[{"operator":0,"value":{"mul":"1000"}}],"symbol":"kHz"},{"code":"MEGAHERTZ","convert":[{"operator":0,"value":{"mul":"1000000"}}],"symbol":"MHz"},{"code":"GIGAHERTZ","convert":[{"operator":0,"value":{"mul":"1000000000"}}],"symbol":"GHz"},{"code":"TERAHERTZ","convert":[{"operator":0,"value":{"mul":"1000000000000"}}],"symbol":"THz"}]'),
    ('Length', 'METER', '[{"code":"MILLIMETER","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"mm"},{"code":"CENTIMETER","convert":[{"operator":0,"value":{"mul":"0.01"}}],"symbol":"cm"},{"code":"DECIMETER","convert":[{"operator":0,"value":{"mul":"0.1"}}],"symbol":"dm"},{"code":"METER","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"m"},{"code":"DEKAMETER","convert":[{"operator":0,"value":{"mul":"10"}}],"symbol":"dam"},{"code":"HECTOMETER","convert":[{"operator":0,"value":{"mul":"100"}}],"symbol":"hm"},{"code":"KILOMETER","convert":[{"operator":0,"value":{"mul":"1000"}}],"symbol":"km"},{"code":"MIL","convert":[{"operator":0,"value":{"mul":"0.0000254"}}],"symbol":"mil"},{"code":"INCH","convert":[{"operator":0,"value":{"mul":"0.0254"}}],"symbol":"in"},{"code":"FEET","convert":[{"operator":0,"value":{"mul":"0.3048"}}],"symbol":"ft"},{"code":"YARD","convert":[{"operator":0,"value":{"mul":"0.9144"}}],"symbol":"yd"},{"code":"CHAIN","convert":[{"operator":0,"value":{"mul":"20.1168"}}],"symbol":"ch"},{"code":"FURLONG","convert":[{"operator":0,"value":{"mul":"201.168"}}],"symbol":"fur"},{"code":"MILE","convert":[{"operator":0,"value":{"mul":"1609.344"}}],"symbol":"mi"}]'),
    ('Power', 'WATT', '[{"code":"WATT","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"W"},{"code":"KILOWATT","convert":[{"operator":0,"value":{"mul":"1000"}}],"symbol":"kW"},{"code":"MEGAWATT","convert":[{"operator":0,"value":{"mul":"1000000"}}],"symbol":"MW"},{"code":"GIGAWATT","convert":[{"operator":0,"value":{"mul":"1000000000"}}],"symbol":"GW"},{"code":"TERAWATT","convert":[{"operator":0,"value":{"mul":"1000000000000"}}],"symbol":"TW"}]'),
    ('Voltage', 'VOLT', '[{"code":"MILLIVOLT","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"mV"},{"code":"CENTIVOLT","convert":[{"operator":0,"value":{"mul":"0.01"}}],"symbol":"cV"},{"code":"DECIVOLT","convert":[{"operator":0,"value":{"mul":"0.1"}}],"symbol":"dV"},{"code":"VOLT","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"V"},{"code":"DEKAVOLT","convert":[{"operator":0,"value":{"mul":"10"}}],"symbol":"daV"},{"code":"HECTOVOLT","convert":[{"operator":0,"value":{"mul":"100"}}],"symbol":"hV"},{"code":"KILOVOLT","convert":[{"operator":0,"value":{"mul":"1000"}}],"symbol":"kV"}]'),
    ('Intensity', 'AMPERE', '[{"code":"MILLIAMPERE","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"mA"},{"code":"CENTIAMPERE","convert":[{"operator":0,"value":{"mul":"0.01"}}],"symbol":"cA"},{"code":"DECIAMPERE","convert":[{"operator":0,"value":{"mul":"0.1"}}],"symbol":"dA"},{"code":"AMPERE","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"A"},{"code":"DEKAMPERE","convert":[{"operator":0,"value":{"mul":"10"}}],"symbol":"daA"},{"code":"HECTOAMPERE","convert":[{"operator":0,"value":{"mul":"100"}}],"symbol":"hA"},{"code":"KILOAMPERE","convert":[{"operator":0,"value":{"mul":"1000"}}],"symbol":"kA"}]'),
    ('Resistance', 'OHM', '[{"code":"MILLIOHM","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"mΩ"},{"code":"CENTIOHM","convert":[{"operator":0,"value":{"mul":"0.01"}}],"symbol":"cΩ"},{"code":"DECIOHM","convert":[{"operator":0,"value":{"mul":"0.1"}}],"symbol":"dΩ"},{"code":"OHM","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"Ω"},{"code":"DEKAOHM","convert":[{"operator":0,"value":{"mul":"10"}}],"symbol":"daΩ"},{"code":"HECTOHM","convert":[{"operator":0,"value":{"mul":"100"}}],"symbol":"hΩ"},{"code":"KILOHM","convert":[{"operator":0,"value":{"mul":"1000"}}],"symbol":"kΩ"},{"code":"MEGOHM","convert":[{"operator":0,"value":{"mul":"1000000"}}],"symbol":"MΩ"}]'),
    ('Speed', 'METER_PER_SECOND', '[{"code":"METER_PER_SECOND","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"m/s"},{"code":"METER_PER_MINUTE","convert":[{"operator":0,"value":{"div":"60"}}],"symbol":"m/mn"},{"code":"METER_PER_HOUR","convert":[{"operator":0,"value":{"mul":"1"}},{"operator":1,"value":{"div":"3600"}}],"symbol":"m/h"},{"code":"KILOMETER_PER_HOUR","convert":[{"operator":0,"value":{"mul":"1000"}},{"operator":1,"value":{"div":"3600"}}],"symbol":"km/h"},{"code":"FOOT_PER_SECOND","convert":[{"operator":0,"value":{"mul":"0.3048"}}],"symbol":"ft/s"},{"code":"FOOT_PER_HOUR","convert":[{"operator":0,"value":{"mul":"0.3048"}},{"operator":1,"value":{"div":"3600"}}],"symbol":"ft/h"},{"code":"YARD_PER_HOUR","convert":[{"operator":0,"value":{"mul":"0.9144"}},{"operator":1,"value":{"div":"3600"}}],"symbol":"yd/h"},{"code":"MILE_PER_HOUR","convert":[{"operator":0,"value":{"mul":"1609.344"}},{"operator":1,"value":{"div":"3600"}}],"symbol":"mi/h"}]'),
    ('ElectricCharge', 'AMPEREHOUR', '[{"code":"MILLIAMPEREHOUR","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"mAh"},{"code":"AMPEREHOUR","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"Ah"},{"code":"MILLICOULOMB","convert":[{"operator":0,"value":{"div":"3600000"}}],"symbol":"mC"},{"code":"CENTICOULOMB","convert":[{"operator":0,"value":{"div":"360000"}}],"symbol":"cC"},{"code":"DECICOULOMB","convert":[{"operator":0,"value":{"div":"36000"}}],"symbol":"dC"},{"code":"COULOMB","convert":[{"operator":0,"value":{"div":"3600"}}],"symbol":"C"},{"code":"DEKACOULOMB","convert":[{"operator":0,"value":{"div":"360"}}],"symbol":"daC"},{"code":"HECTOCOULOMB","convert":[{"operator":0,"value":{"div":"36"}}],"symbol":"hC"},{"code":"KILOCOULOMB","convert":[{"operator":0,"value":{"div":"3.6"}}],"symbol":"kC"}]'),
    ('Duration', 'SECOND', '[{"code":"MILLISECOND","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"ms"},{"code":"SECOND","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"s"},{"code":"MINUTE","convert":[{"operator":0,"value":{"mul":"60"}}],"symbol":"m"},{"code":"HOUR","convert":[{"operator":0,"value":{"mul":"3600"}}],"symbol":"h"},{"code":"DAY","convert":[{"operator":0,"value":{"mul":"86400"}}],"symbol":"d"},{"code":"WEEK","convert":[{"operator":0,"value":{"mul":"604800"}}],"symbol":"week"},{"code":"MONTH","convert":[{"operator":0,"value":{"mul":"18748800"}}],"symbol":"month"},{"code":"YEAR","convert":[{"operator":0,"value":{"mul":"31536000"}}],"symbol":"year"}]'),
    ('Temperature', 'KELVIN', '[{"code":"CELSIUS","convert":[{"operator":0,"value":{"add":"273.15"}}],"symbol":"°C"},{"code":"FAHRENHEIT","convert":[{"operator":0,"value":{"sub":"32"}},{"operator":1,"value":{"div":"1.8"}},{"operator":2,"value":{"add":"273.15"}}],"symbol":"°F"},{"code":"KELVIN","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"°K"},{"code":"RANKINE","convert":[{"operator":0,"value":{"div":"1.8"}}],"symbol":"°R"},{"code":"REAUMUR","convert":[{"operator":0,"value":{"mul":"1.25"}},{"operator":1,"value":{"add":"273.15"}}],"symbol":"°r"}]'),
    ('Volume', 'CUBIC_METER', '[{"code":"CUBIC_MILLIMETER","convert":[{"operator":0,"value":{"mul":"0.000000001"}}],"symbol":"mm³"},{"code":"CUBIC_CENTIMETER","convert":[{"operator":0,"value":{"mul":"0.000001"}}],"symbol":"cm³"},{"code":"MILLILITER","convert":[{"operator":0,"value":{"mul":"0.000001"}}],"symbol":"ml"},{"code":"CENTILITER","convert":[{"operator":0,"value":{"mul":"0.00001"}}],"symbol":"cl"},{"code":"DECILITER","convert":[{"operator":0,"value":{"mul":"0.0001"}}],"symbol":"dl"},{"code":"CUBIC_DECIMETER","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"dm³"},{"code":"LITER","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"l"},{"code":"CUBIC_METER","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"m³"},{"code":"OUNCE","convert":[{"operator":0,"value":{"mul":"0.00454609"}},{"operator":1,"value":{"div":"160"}}],"symbol":"oz"},{"code":"PINT","convert":[{"operator":0,"value":{"mul":"0.00454609"}},{"operator":1,"value":{"div":"8"}}],"symbol":"pt"},{"code":"BARREL","convert":[{"operator":0,"value":{"mul":"0.16365924"}}],"symbol":"bbl"},{"code":"GALLON","convert":[{"operator":0,"value":{"mul":"0.00454609"}}],"symbol":"gal"},{"code":"CUBIC_FOOT","convert":[{"operator":0,"value":{"mul":"6.54119159"}},{"operator":1,"value":{"div":"231"}}],"symbol":"ft³"},{"code":"CUBIC_INCH","convert":[{"operator":0,"value":{"mul":"0.0037854118"}},{"operator":1,"value":{"div":"231"}}],"symbol":"in³"},{"code":"CUBIC_YARD","convert":[{"operator":0,"value":{"mul":"0.764554861"}}],"symbol":"yd³"}]'),
    ('Weight', 'KILOGRAM', '[{"code":"MILLIGRAM","convert":[{"operator":0,"value":{"mul":"0.000001"}}],"symbol":"mg"},{"code":"GRAM","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"g"},{"code":"KILOGRAM","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"kg"},{"code":"TON","convert":[{"operator":0,"value":{"mul":"1000"}}],"symbol":"t"},{"code":"GRAIN","convert":[{"operator":0,"value":{"mul":"0.00006479891"}}],"symbol":"gr"},{"code":"DENIER","convert":[{"operator":0,"value":{"mul":"0.001275"}}],"symbol":"denier"},{"code":"ONCE","convert":[{"operator":0,"value":{"mul":"0.03059"}}],"symbol":"once"},{"code":"MARC","convert":[{"operator":0,"value":{"mul":"0.24475"}}],"symbol":"marc"},{"code":"LIVRE","convert":[{"operator":0,"value":{"mul":"0.4895"}}],"symbol":"livre"},{"code":"OUNCE","convert":[{"operator":0,"value":{"mul":"0.45359237"}},{"operator":1,"value":{"div":"16"}}],"symbol":"oz"},{"code":"POUND","convert":[{"operator":0,"value":{"mul":"0.45359237"}}],"symbol":"lb"}]'),
    ('Pressure', 'BAR', '[{"code":"BAR","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"Bar"},{"code":"PASCAL","convert":[{"operator":0,"value":{"mul":"0.00001"}}],"symbol":"Pa"},{"code":"HECTOPASCAL","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"hPa"},{"code":"MILLIBAR","convert":[{"operator":0,"value":{"mul":"0.001"}}],"symbol":"mBar"},{"code":"ATM","convert":[{"operator":0,"value":{"mul":"0.986923"}}],"symbol":"atm"},{"code":"PSI","convert":[{"operator":0,"value":{"mul":"14.50376985373022"}}],"symbol":"PSI"},{"code":"TORR","convert":[{"operator":0,"value":{"mul":"750.06375541921"}}],"symbol":"Torr"},{"code":"MMHG","convert":[{"operator":0,"value":{"mul":"750.06375541921"}}],"symbol":"mmHg"}]'),
    ('Energy', 'JOULE', '[{"code":"JOULE","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"J"},{"code":"CALORIE","convert":[{"operator":0,"value":{"mul":"4.184"}}],"symbol":"cal"},{"code":"KILOCALORIE","convert":[{"operator":0,"value":{"mul":"4184"}}],"symbol":"kcal"},{"code":"KILOJOULE","convert":[{"operator":0,"value":{"mul":"1000"}}],"symbol":"kJ"}]'),
    ('CaseBox', 'PIECE', '[{"code":"PIECE","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"Pc"},{"code":"DOZEN","convert":[{"operator":0,"value":{"mul":"12"}}],"symbol":"Dz"}]'),
    ('Brightness', 'LUMEN', '[{"code":"LUMEN","convert":[{"operator":0,"value":{"mul":"1"}}],"symbol":"lm"},{"code":"NIT","convert":[{"operator":0,"value":{"mul":"0.2918855809"}}],"symbol":"nits"}]');
SQL;

        $this->connection->exec($sql);
    }
}
