<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Metadata;

use Oro\Bundle\SecurityBundle\Metadata\ActionMetadataProvider;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;

class ActionMetadataProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $annotationProvider;

    /** @var ActionMetadataProvider */
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

        $this->annotationProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ActionMetadataProvider($this->annotationProvider);
    }

    public function testGetActions()
    {
        $this->annotationProvider->expects($this->once())
            ->method('getAnnotations')
            ->with($this->equalTo('action'))
            ->will(
                $this->returnValue(
                    array(
                        new AclAnnotation(
                            array(
                                'id' => 'test',
                                'type' => 'action',
                                'group_name' => 'TestGroup',
                                'label' => 'TestLabel'
                            )
                        )
                    )
                )
            );

        $actions = $this->provider->getActions();
        $this->assertCount(1, $actions);
        $this->assertEquals('test', $actions[0]->getClassName());
        $this->assertEquals('TestGroup', $actions[0]->getGroup());
        $this->assertEquals('TestLabel', $actions[0]->getLabel());
    }
}
