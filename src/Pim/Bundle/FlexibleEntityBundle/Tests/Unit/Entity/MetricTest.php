<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Test extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->metric = new Metric();
    }

    /**
     * @dataProvider getDataAsStringData
     */
    public function testGetDataAsString($data, $result)
    {
        $this->metric->setData($data);

        $this->assertEquals($result, $this->metric->getDataAsString());
    }

    public static function getDataAsStringData()
    {
        return array(
            array(1000.0000, '1000'),
            array(0.0010,    '0.001'),
            array(0.0000,    '0'),
            array(null,      ''),
        );
    }

    /**
     * @dataProvider getToStringData
     */
    public function testToString($data, $unit, $result)
    {
        $this->metric
            ->setData($data)
            ->setUnit($unit);

        $this->assertEquals($result, (string) $this->metric);
    }

    public static function getToStringData()
    {
        return array(
            array(1000.0000, 'GRAM',      '1000 GRAM'),
            array(0.0010,    'KILOGRAM',  '0.001 KILOGRAM'),
            array(0.0000,    'MILLIGRAM', '0 MILLIGRAM'),
            array(null,      'TON',       ''),
        );
    }
}
