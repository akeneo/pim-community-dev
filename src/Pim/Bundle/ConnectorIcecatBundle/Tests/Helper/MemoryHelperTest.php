<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Helper;

use Pim\Bundle\ConnectorIcecatBundle\Helper\MemoryHelper;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test for MemoryHelper utility class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class MemoryHelperTest extends KernelAwareTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        MemoryHelper::resetAllPoints();
    }

    /**
     * Test add point method
     */
    public function testAddValue()
    {
        // add a value for a point
        $pointName = 'test-add-point';
        MemoryHelper::addValue($pointName);
        $values = MemoryHelper::getValues($pointName);
        $this->assertCount(1, $values);

        // add a second value for the same point
        MemoryHelper::addValue($pointName);
        $values = MemoryHelper::getValues($pointName);
        $this->assertCount(2, $values);

        // add a value for another point
        $pointName2 = 'test-add-point-2';
        MemoryHelper::addValue($pointName2);
        $values = MemoryHelper::getValues($pointName2);
        $this->assertCount(1, $values);
    }

    /**
     * Test write point method
     */
    public function testWritePoint()
    {
        $str = MemoryHelper::writeValue('test-write-point');
        $this->assertStringMatchesFormat('%f Mo', $str);
    }

    /**
     * Test write gap method
     */
    public function testWriteGap()
    {
        // create a first point
        MemoryHelper::addValue('test-write-gap');
        // write a gap
        $str = MemoryHelper::writeGap('test-write-gap');
        $this->assertStringMatchesFormat('%f Mo', $str);

        // TODO : Test write gap on an unexisting point name -> failed actually

        // TODO : Test write gap add a value
    }

    /**
     * Test get all values method
     */
    public function testgetValues()
    {
        $pointName = 'test-all-values';

        // add some points
        MemoryHelper::addValue($pointName);
        MemoryHelper::addValue($pointName);
        MemoryHelper::addValue($pointName);
        MemoryHelper::addValue($pointName);
        MemoryHelper::addValue($pointName);

        $values = MemoryHelper::getValues($pointName);
        $this->assertCount(5, $values);
    }

    /**
     * Test reset point
     */
    public function testResetPoint()
    {
        $pointName1 = 'test-reset-point-1';
        $pointName2 = 'test-reset-point-2';

        // add points
        MemoryHelper::addValue($pointName1);
        MemoryHelper::addValue($pointName1);
        MemoryHelper::addValue($pointName1);
        MemoryHelper::addValue($pointName2);

        MemoryHelper::resetPoint($pointName1);

        // assert count
        $values = MemoryHelper::getValues($pointName1);
        $this->assertCount(0, $values);

        $values = MemoryHelper::getValues($pointName2);
        $this->assertCount(1, $values);

        $points = MemoryHelper::getInstance();
        $this->assertCount(2, $points);
    }

    /**
     * Test reset all point
     */
    public function testResetAllPoint()
    {
        $pointName1 = 'reset-all-points-1';
        $pointName2 = 'reset-all-points-2';

        // add points
        MemoryHelper::addValue($pointName1);
        MemoryHelper::addValue($pointName1);
        MemoryHelper::addValue($pointName1);
        MemoryHelper::addValue($pointName2);

        MemoryHelper::resetAllPoints();

        // assert count
        $points = MemoryHelper::getInstance();
        $this->assertCount(0, $points);

        // TODO : test getValues but there are bugs index actually
    }
}