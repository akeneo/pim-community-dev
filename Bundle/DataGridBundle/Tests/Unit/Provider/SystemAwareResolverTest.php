<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Provider;

use Oro\Bundle\DataGridBundle\Provider\SystemAwareResolver;
use Oro\Bundle\DataGridBundle\Tests\Unit\DataFixtures\Stub\SomeClass;

class SystemAwareResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var SystemAwareResolver */
    protected $resolver;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $container;

    /**
     * setup mock and test object
     */
    public function setUp()
    {
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->resolver = new SystemAwareResolver($this->container);
    }

    /**
     * Test resolve method
     *
     * @param $gridName
     * @param $gridDefinition
     * @param $expect
     *
     * @dataProvider resolveProvider
     */
    public function testResolve($gridName, $gridDefinition, $expect)
    {
        $this->markTestSkipped("TODO Fix");

        if ($gridName == 'test1') {
            $this->container->expects($this->once())
                ->method('get')
                ->with('oro_datagrid.some_class')
                ->will($this->returnValue(new SomeClass()));
        }

        if ($gridName == 'test2') {
            $this->container->expects($this->once())
                ->method('getParameter')
                ->with('oro_datagrid.some.class')
                ->will($this->returnValue('Oro\Bundle\DataGridBundle\Tests\Unit\DataFixtures\Stub\SomeClass'));
        }

        $gridDefinition = $this->resolver->resolve($gridName, $gridDefinition);

        $this->assertEquals($expect, $gridDefinition['filters']['entityName']['choices']);
    }

    /**
     * Assert definition empty
     */
    public function testResolveEmpty()
    {
        $definition = [];
        $gridDefinition = $this->resolver->resolve('test', $definition);

        $this->assertEmpty($gridDefinition);

        $definition = array(
            'filters' => array(
                'entityName' => array(
                    'choices' => 'test-not-valid',
                )
            )
        );
        $gridDefinition = $this->resolver->resolve('test', $definition);
        $this->assertEquals($definition, $gridDefinition);
    }

    /**
     * Data provider for testResolve
     */
    public function resolveProvider()
    {
        return array(
            'service method call' => array(
                'test1',
                array(
                    'filters' => array(
                        'entityName' => array(
                            'choices' => '@oro_datagrid.some_class->getAnswerToLifeAndEverything',
                        )
                    )
                ),
                42
            ),

            'static call' => array(
                'test2',
                array(
                    'filters' => array(
                        'entityName' => array(
                            'choices' => '%oro_datagrid.some.class%::testStaticCall',
                        )
                    )
                ),
                84
            ),

            'class constant' => array(
                'test3',
                array(
                    'filters' => array(
                        'entityName' => array(
                            'choices' => 'Oro\Bundle\DataGridBundle\Tests\Unit\DataFixtures\Stub\SomeClass::TEST',
                        )
                    )
                ),
                42
            )
        );
    }
}
