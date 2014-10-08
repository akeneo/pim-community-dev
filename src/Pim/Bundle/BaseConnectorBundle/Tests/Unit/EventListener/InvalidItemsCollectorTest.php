<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\EventListener;

use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidItemsCollectorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->collector = new InvalidItemsCollector();
    }

    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->collector);
    }

    public function testSubscribedToInvalidItem()
    {
        $this->assertEquals(
            array(EventInterface::INVALID_ITEM => 'collect'),
            InvalidItemsCollector::getSubscribedEvents()
        );
    }

    public function testCollectInvalidItem()
    {
        $event = $this->getInvalidItemEventMock(
            array(
                'sku'  => 'foo',
                'name' => 'b@r',
            )
        );

        $this->collector->collect($event);

        $this->assertEquals(
            array(
                '3067c955e49d30d7b352a8e7751f36c4' => array(
                    'sku'  => 'foo',
                    'name' => 'b@r',
                )
            ),
            $this->collector->getInvalidItems()
        );
    }

    protected function getInvalidItemEventMock(array $item)
    {
        $event = $this
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Event\InvalidItemEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getItem')
            ->will($this->returnValue($item));

        return $event;
    }
}
