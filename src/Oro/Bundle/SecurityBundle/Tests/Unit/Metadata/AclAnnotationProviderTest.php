<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Metadata;

use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;

class AclAnnotationProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $loader;

    /** @var AclAnnotationProvider */
    protected $provider;

    protected function setUp()
    {
        $this->cache = $this->getMockForAbstractClass(
            'Doctrine\Common\Cache\CacheProvider',
            array(),
            '',
            false,
            true,
            true,
            array('fetch', 'save', 'delete', 'deleteAll')
        );
        $this->loader = $this->getMock('Oro\Bundle\SecurityBundle\Annotation\Loader\AclAnnotationLoaderInterface');
        $this->provider = new AclAnnotationProvider($this->cache);
        $this->provider->addLoader($this->loader);
    }

    public function testFindAndGetAnnotation()
    {
        $this->loader->expects($this->once())
            ->method('load')
            ->will(
                $this->returnCallback(
                    function ($storage) {
                        /** @var AclAnnotationStorage $storage */
                        $storage->add(
                            new AclAnnotation(array('id' => 'test', 'type' => 'entity')),
                            'SomeClass',
                            'SomeMethod'
                        );
                    }
                )
            );

        $this->assertEquals('test', $this->provider->findAnnotationById('test')->getId());
        $this->assertNull($this->provider->findAnnotationById('unknown'));

        $this->assertEquals('test', $this->provider->findAnnotation('SomeClass', 'SomeMethod')->getId());
        $this->assertNull($this->provider->findAnnotation('SomeClass', 'UnknownMethod'));
        $this->assertNull($this->provider->findAnnotation('UnknownClass', 'SomeMethod'));

        $this->assertCount(1, $this->provider->getAnnotations());
        $this->assertCount(1, $this->provider->getAnnotations('entity'));
        $this->assertCount(0, $this->provider->getAnnotations('action'));
    }

    public function testHasAnnotationAndIsProtected()
    {
        $this->loader->expects($this->once())
            ->method('load')
            ->will(
                $this->returnCallback(
                    function ($storage) {
                        /** @var AclAnnotationStorage $storage */
                        $storage->add(
                            new AclAnnotation(array('id' => 'test', 'type' => 'entity')),
                            'SomeClass',
                            'SomeMethod'
                        );
                    }
                )
            );

        $this->assertFalse($this->provider->hasAnnotation('SomeClass'));
        $this->assertFalse($this->provider->hasAnnotation('UnknownClass'));
        $this->assertTrue($this->provider->hasAnnotation('SomeClass', 'SomeMethod'));
        $this->assertFalse($this->provider->hasAnnotation('SomeClass', 'UnknownMethod'));
        $this->assertFalse($this->provider->hasAnnotation('UnknownClass', 'SomeMethod'));
    }

    public function testCache()
    {
        $this->loader->expects($this->exactly(2))
            ->method('load');
        $this->cache->expects($this->at(0))
            ->method('fetch');
        $this->cache->expects($this->at(1))
            ->method('save');
        $this->cache->expects($this->at(2))
            ->method('delete');
        $this->cache->expects($this->at(3))
            ->method('fetch');

        $this->provider->warmUpCache();
        $this->provider->clearCache();
        $this->assertNull($this->provider->findAnnotationById('unknown'));
    }
}
