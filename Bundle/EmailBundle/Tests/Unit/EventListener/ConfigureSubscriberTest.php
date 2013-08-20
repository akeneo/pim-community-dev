<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EntityConfigBundle\Event\NewEntityEvent;
use Oro\Bundle\EmailBundle\EventListener\ConfigSubscriber;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;

class ConfigureSubscriberTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CACHE_KEY  = 'testCache.Key';
    const TEST_CLASS_NAME = 'someClassName';

    /** @var ConfigSubscriber */
    protected $subscriber;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    public function setUp()
    {
        $this->cache = $this->getMockBuilder('Doctrine\Common\Cache\Cache')
            ->disableOriginalConstructor()->getMock();

        $this->subscriber = new ConfigSubscriber($this->cache, self::TEST_CACHE_KEY);
    }

    public function tearDown()
    {
        unset($this->cache);
        unset($this->subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $result = ConfigSubscriber::getSubscribedEvents();

        foreach ($result as $eventProcessMethod) {
            $this->assertTrue(is_callable(array($this->subscriber, $eventProcessMethod)));
        }
    }

    /**
     * @dataProvider newEntityFieldsProvider
     * @param ArrayCollection $fieldsCollection
     * @param $shouldClearCache
     */
    public function testNewEntityConfig(ArrayCollection $fieldsCollection, $shouldClearCache)
    {
        $cmMock = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()->getMock();

        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\EntityConfig')
            ->disableOriginalConstructor()->getMock();
        $config->expects($this->once())->method('getFields')
            ->will(
                $this->returnCallback(
                    function ($callback) use ($fieldsCollection) {
                        return $fieldsCollection->filter($callback);
                    }
                )
            );

        $cmMock->expects($this->once())->method('hasConfig')->with(self::TEST_CLASS_NAME)
            ->will($this->returnValue(true));
        $cmMock->expects($this->once())->method('getConfig')->with(self::TEST_CLASS_NAME)
            ->will($this->returnValue($config));
        $event = new NewEntityEvent(self::TEST_CLASS_NAME, $cmMock);

        $this->cache->expects($this->exactly((int)$shouldClearCache))->method('delete');

        $this->subscriber->newEntityConfig($event);
    }

    /**
     * @return array
     */
    public function newEntityFieldsProvider()
    {
        $field = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\FieldConfig')
            ->disableOriginalConstructor()->getMock();
        $field->expects($this->at(0))->method('is')->with('available_in_template')
            ->will($this->returnValue(true));
        $field->expects($this->at(1))->method('is')->with('available_in_template')
            ->will($this->returnValue(false));
        $field->expects($this->at(2))->method('is')->with('available_in_template')
            ->will($this->returnValue(false));

        return array(
            'should clear cache' => array(
                new ArrayCollection(array($field, $field)),
                true
            ),
            'cache should not be cleared' => array(
                new ArrayCollection(array($field)),
                false
            )
        );
    }

    /**
     * @dataProvider changeSetProvider
     * @param $scope
     * @param $change
     * @param $shouldClearCache
     */
    public function testPersistConfig($scope, $change, $shouldClearCache)
    {
        $cmMock = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()->getMock();
        $cmMock->expects($this->once())->method('calculateConfigChangeSet');
        $cmMock->expects($this->once())->method('getConfigChangeSet')
            ->will($this->returnValue($change));

        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\EntityConfig')
            ->disableOriginalConstructor()->getMock();
        $config->expects($this->once())->method('getScope')
            ->will($this->returnValue($scope));

        $event = new PersistConfigEvent($config, $cmMock);
        $this->cache->expects($this->exactly((int)$shouldClearCache))->method('delete');

        $this->subscriber->persistConfig($event);
    }

    /**
     * @return array
     */
    public function changeSetProvider()
    {
        return array(
            'email config changed' => array(
                'scope'            => 'email',
                'change'           => array('available_in_template' => array()),
                'shouldClearCache' => true
            ),
            'email config not changed' => array(
                'scope'            => 'email',
                'change'           => array(),
                'shouldClearCache' => false
            ),
            'not email config' => array(
                'scope'            => 'someConfigScope',
                'change'           => array(),
                'shouldClearCache' => false
            )
        );
    }
}
