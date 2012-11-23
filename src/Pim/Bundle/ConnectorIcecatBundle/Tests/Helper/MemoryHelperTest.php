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
     * Test add event method
     */
    public function testAddValue()
    {
        // add a value for a event
        $eventName = 'test-add-event';
        MemoryHelper::addValue($eventName);
        $values = MemoryHelper::getValues($eventName);
        $this->assertCount(1, $values);

        // add a second value for the same event
        MemoryHelper::addValue($eventName);
        $values = MemoryHelper::getValues($eventName);
        $this->assertCount(2, $values);

        // add a value for another event
        $eventName2 = 'test-add-event-2';
        MemoryHelper::addValue($eventName2);
        $values = MemoryHelper::getValues($eventName2);
        $this->assertCount(1, $values);
    }

    /**
     * Test write event method
     */
    public function testWritePoint()
    {
        $str = MemoryHelper::writeValue('test-write-event');
        $this->assertStringMatchesFormat('%f Mo', $str);
    }

    /**
     * Test write gap method
     */
    public function testWriteGap()
    {
        // create a first event
        MemoryHelper::addValue('test-write-gap');
        // write a gap
        $str = MemoryHelper::writeGap('test-write-gap');
        $this->assertStringMatchesFormat('%f Mo (%f Mo)', $str);

        // TODO : Test write gap on an unexisting event name -> failed actually

        // TODO : Test write gap add a value
    }

    /**
     * Test get all values method
     */
    public function testgetValues()
    {
        $eventName = 'test-all-values';

        // add some events
        MemoryHelper::addValue($eventName);
        MemoryHelper::addValue($eventName);
        MemoryHelper::addValue($eventName);
        MemoryHelper::addValue($eventName);
        MemoryHelper::addValue($eventName);

        $values = MemoryHelper::getValues($eventName);
        $this->assertCount(5, $values);
    }

    /**
     * Test reset event
     */
    public function testResetPoint()
    {
        $eventName1 = 'test-reset-event-1';
        $eventName2 = 'test-reset-event-2';

        // add events
        MemoryHelper::addValue($eventName1);
        MemoryHelper::addValue($eventName1);
        MemoryHelper::addValue($eventName1);
        MemoryHelper::addValue($eventName2);

        MemoryHelper::resetPoint($eventName1);

        // assert count
        $values = MemoryHelper::getValues($eventName1);
        $this->assertCount(0, $values);

        $values = MemoryHelper::getValues($eventName2);
        $this->assertCount(1, $values);

        $events = MemoryHelper::getInstance();
        $this->assertCount(2, $events);
    }

    /**
     * Test reset all event
     */
    public function testResetAllPoint()
    {
        $eventName1 = 'test-reset-all-events-1';
        $eventName2 = 'test-reset-all-events-2';

        // add events
        MemoryHelper::addValue($eventName1);
        MemoryHelper::addValue($eventName1);
        MemoryHelper::addValue($eventName1);
        MemoryHelper::addValue($eventName2);

        MemoryHelper::resetAllPoints();

        // assert count
        $events = MemoryHelper::getInstance();
        $this->assertCount(0, $events);

        // TODO : test getValues but there are bugs index actually
    }
}