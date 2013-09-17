<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\ImportExportBundle\DependencyInjection\Compiler\AddNormalizerCompilerPass;

class AddNormalizerCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializerDefinition;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $containerBuilder;

    protected function setUp()
    {
        $this->serializerDefinition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $this->containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($taggedServices, $expectedNormalizers)
    {
        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with(AddNormalizerCompilerPass::SERIALIZER_SERVICE)
            ->will($this->returnValue($this->serializerDefinition));

        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(AddNormalizerCompilerPass::ATTRIBUTE_NORMALIZER_TAG)
            ->will($this->returnValue($taggedServices));

        $this->serializerDefinition->expects($this->once())
            ->method('replaceArgument')
            ->with(0, $expectedNormalizers);

        $pass = new AddNormalizerCompilerPass();
        $pass->process($this->containerBuilder);
    }

    public function processDataProvider()
    {
        return array(
            'sort_by_priority' => array(
                'taggedServices' => array(
                    'foo_1' => array(array('priority' => 1)),
                    'bar_0' => array(array()),
                    'baz_2' => array(array('priority' => 2)),
                ),
                'expectedNormalizers' => array(
                    new Reference('baz_2'),
                    new Reference('foo_1'),
                    new Reference('bar_0'),
                )
            ),
            'default_order' => array(
                'taggedServices' => array(
                    'foo' => array(array()),
                    'bar' => array(array()),
                    'baz' => array(array()),
                ),
                'expectedNormalizers' => array(
                    new Reference('foo'),
                    new Reference('bar'),
                    new Reference('baz'),
                )
            ),
        );
    }

    //@codingStandardsIgnoreStart
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You must tag at least one service as "oro_importexport.normalizer" to use the import export Serializer service
     */
    // @codingStandardIgnoreEnd
    public function testProcessFailsWhenNoNormalizers()
    {
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(AddNormalizerCompilerPass::ATTRIBUTE_NORMALIZER_TAG)
            ->will($this->returnValue(array()));

        $pass = new AddNormalizerCompilerPass();
        $pass->process($this->containerBuilder);
    }
}
