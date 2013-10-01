<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;
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
        $cmMock = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()->getMock();

        $cpMock = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $cpMock->expects($this->once())->method('filter')
            ->will(
                $this->returnCallback(
                    function ($callback) use ($fieldsCollection) {
                        return $fieldsCollection->filter($callback);
                    }
                )
            );

        $cmMock->expects($this->once())->method('getProvider')
            ->with('email')
            ->will($this->returnValue($cpMock));

        $entityModel = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new NewEntityConfigModelEvent($entityModel, $cmMock);

        $this->cache->expects($this->exactly((int)$shouldClearCache))->method('delete');

        $this->subscriber->newEntityConfig($event);
    }

    /**
     * @return array
     */
    public function newEntityFieldsProvider()
    {
        $config = $this->getMockForAbstractClass('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $config->expects($this->at(0))->method('is')->with('available_in_template')
            ->will($this->returnValue(true));
        $config->expects($this->at(1))->method('is')->with('available_in_template')
            ->will($this->returnValue(false));
        $config->expects($this->at(2))->method('is')->with('available_in_template')
            ->will($this->returnValue(false));

        return array(
            'should clear cache' => array(
                new ArrayCollection(array($config, $config)),
                true
            ),
            'cache should not be cleared' => array(
                new ArrayCollection(array($config)),
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
        $cmMock = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()->getMock();
        $cmMock->expects($this->once())->method('calculateConfigChangeSet');
        $cmMock->expects($this->once())->method('getConfigChangeSet')
            ->will($this->returnValue($change));

        $configId = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface')
            ->disableOriginalConstructor()->getMock();

        $configId->expects($this->once())->method('getScope')
            ->will($this->returnValue($scope));

        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->disableOriginalConstructor()->getMock();

        $config->expects($this->once())->method('getId')
            ->will($this->returnValue($configId));

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
