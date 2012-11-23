<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Helper;

use Pim\Bundle\ConnectorIcecatBundle\Helper\TimeHelper;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test for TimeHelper utility class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TimeHelperTest extends KernelAwareTest
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
        TimeHelper::resetAllPoints();
    }

    /**
     * Test add event method
     */
    public function testAddValue()
    {
        // add a value for a event
        $eventName = 'test-add-event';
        TimeHelper::addValue($eventName);
        $values = TimeHelper::getValues($eventName);
        $this->assertCount(1, $values);

        // add a second value for the same event
        TimeHelper::addValue($eventName);
        $values = TimeHelper::getValues($eventName);
        $this->assertCount(2, $values);

        // add a value for another event
        $eventName2 = 'test-add-event-2';
        TimeHelper::addValue($eventName2);
        $values = TimeHelper::getValues($eventName2);
        $this->assertCount(1, $values);
    }

    /**
     * Test write event method
     */
    public function testWritePoint()
    {
        $str = TimeHelper::writeValue('test-write-event');
        $this->assertStringMatchesFormat('%f secs', $str);
    }

    /**
     * Test write gap method
     */
    public function testWriteGap()
    {
        // create a first event
        TimeHelper::addValue('test-write-gap');
        // write a gap
        $str = TimeHelper::writeGap('test-write-gap');
        $this->assertStringMatchesFormat('%f secs', $str);

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
        TimeHelper::addValue($eventName);
        TimeHelper::addValue($eventName);
        TimeHelper::addValue($eventName);
        TimeHelper::addValue($eventName);
        TimeHelper::addValue($eventName);

        $values = TimeHelper::getValues($eventName);
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
        TimeHelper::addValue($eventName1);
        TimeHelper::addValue($eventName1);
        TimeHelper::addValue($eventName1);
        TimeHelper::addValue($eventName2);

        TimeHelper::resetPoint($eventName1);

        // assert count
        $values = TimeHelper::getValues($eventName1);
        $this->assertCount(0, $values);

        $values = TimeHelper::getValues($eventName2);
        $this->assertCount(1, $values);

        $events = TimeHelper::getInstance();
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
        TimeHelper::addValue($eventName1);
        TimeHelper::addValue($eventName1);
        TimeHelper::addValue($eventName1);
        TimeHelper::addValue($eventName2);

        TimeHelper::resetAllPoints();

        // assert count
        $events = TimeHelper::getInstance();
        $this->assertCount(0, $events);

        // TODO : test getValues but there are bugs index actually
    }
}