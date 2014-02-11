<?php

namespace Akeneo\Bundle\MeasureBundle\Tests\Unit\Manager;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;

class MeasureManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->measureManager = new MeasureManager;
        $this->measureManager->setMeasureConfig(
            array(
                'WEIGHT' => array(
                    'standard' => 'KILOGRAM',
                    'units' => array(
                        'KILOGRAM' => array('symbol' => 'kg'),
                        'GRAM'     => array('symbol' => 'g')
                    )
                )
            )
        );
    }

    public function testGetUnitForFamily()
    {
        $this->assertEquals(
            array(
                'KILOGRAM' => 'kg',
                'GRAM'     => 'g',
            ),
            $this->measureManager->getUnitSymbolsForFamily('WEIGHT')
        );
    }

    public function testInvalidFamilyWhenGettingUnitForFamily()
    {
        try {
            $this->measureManager->getUnitSymbolsForFamily('LENGTH');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Undefined measure family "LENGTH"', $e->getMessage());
            return;
        }

        $this->fail('An InvalidArgumentException has not been raised.');
    }

    public function testGetStandardUnitForFamily()
    {
        $this->assertEquals(
            'KILOGRAM',
            $this->measureManager->getStandardUnitForFamily('WEIGHT')
        );
    }
}
