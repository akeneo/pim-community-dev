<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Metadata;

use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use Oro\Bundle\SecurityBundle\Metadata\ActionMetadata;
use Oro\Bundle\SecurityBundle\Metadata\ActionMetadataProvider;

class ActionMetadataProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $annotationProvider;

    /** @var ActionMetadataProvider */
    protected $provider;

    protected function setUp(): void
    {
        $this->cache = $this->getMockForAbstractClass(
            'Doctrine\Common\Cache\CacheProvider',
            [],
            '',
            false,
            true,
            true,
            ['fetch', 'save', 'delete', 'deleteAll']
        );

        $this->annotationProvider = $this->getMockBuilder(AclAnnotationProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ActionMetadataProvider($this->annotationProvider, $this->cache);
    }

    public function testIsKnownAction()
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with(ActionMetadataProvider::CACHE_KEY)
            ->will($this->returnValue(['SomeAction' => new ActionMetadata()]));

        $this->assertTrue($this->provider->isKnownAction('SomeAction'));
        $this->assertFalse($this->provider->isKnownAction('UnknownAction'));
    }

    public function testGetActions()
    {
        $this->annotationProvider->expects($this->once())
            ->method('getAnnotations')
            ->with($this->equalTo('action'))
            ->will(
                $this->returnValue(
                    [
                        new AclAnnotation(
                            [
                                'id'         => 'test',
                                'type'       => 'action',
                                'group_name' => 'TestGroup',
                                'label'      => 'TestLabel'
                            ]
                        )
                    ]
                )
            );

        $action = new ActionMetadata('test', 'TestGroup', 'TestLabel');

        $this->cache->expects($this->at(0))
            ->method('fetch')
            ->with(ActionMetadataProvider::CACHE_KEY)
            ->will($this->returnValue(false));
        $this->cache->expects($this->at(2))
            ->method('fetch')
            ->with(ActionMetadataProvider::CACHE_KEY)
            ->will($this->returnValue(['test' => $action]));
        $this->cache->expects($this->once())
            ->method('save')
            ->with(ActionMetadataProvider::CACHE_KEY, ['test' => $action]);

        // call without cache
        $actions = $this->provider->getActions();
        $this->assertCount(1, $actions);
        $this->assertEquals($action, $actions[0]);

        // call with local cache
        $actions = $this->provider->getActions();
        $this->assertCount(1, $actions);
        $this->assertEquals($action, $actions[0]);

        // call with cache
        $provider = new ActionMetadataProvider($this->annotationProvider, $this->cache);
        $actions = $provider->getActions();
        $this->assertCount(1, $actions);
        $this->assertEquals($action, $actions[0]);
    }

    public function testCache()
    {
        $this->annotationProvider->expects($this->exactly(2))
            ->method('getAnnotations')
            ->with($this->equalTo('action'))
            ->will($this->returnValue([]));
        $this->cache->expects($this->at(0))
            ->method('fetch')
            ->with(ActionMetadataProvider::CACHE_KEY);
        $this->cache->expects($this->at(1))
            ->method('save')
            ->with(ActionMetadataProvider::CACHE_KEY);
        $this->cache->expects($this->at(2))
            ->method('delete')
            ->with(ActionMetadataProvider::CACHE_KEY);
        $this->cache->expects($this->at(3))
            ->method('fetch')
            ->with(ActionMetadataProvider::CACHE_KEY);

        $this->provider->warmUpCache();
        $this->provider->clearCache();
        $this->assertFalse($this->provider->isKnownAction('unknown'));
    }
}
