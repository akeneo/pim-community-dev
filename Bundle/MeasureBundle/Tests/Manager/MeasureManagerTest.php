<?php

namespace Oro\Bundle\MeasureBundle\Tests\Manager;

use Oro\Bundle\MeasureBundle\Manager\MeasureManager;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasureManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->measureManager = new MeasureManager;
        $this->measureManager->setMeasureConfig(array(
            'WEIGHT' => array(
                'units' => array(
                    'KILOGRAM' => array(
                        'symbol' => 'kg',
                    ),
                    'GRAM' => array(
                        'symbol' => 'g',
                    ),
                )
            )
        ));
    }

    public function testGetUnitForFamily()
    {
        $this->assertEquals(array(
            'KILOGRAM' => 'kg',
            'GRAM'     => 'g',
        ), $this->measureManager->getUnitSymbolsForFamily('WEIGHT'));
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
}
