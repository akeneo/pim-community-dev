<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\DependencyInjection\Compiler;

use Pim\Bundle\TransformBundle\DependencyInjection\Compiler\ReplacePimSerializerArgumentsPass;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplacePimSerializerArgumentsPassTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    private $pass;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->factory = $this->getMock('Pim\Bundle\ImportExportBundle\DependencyInjection\Reference\ReferenceFactory');
        $this->pass = new ReplacePimSerializerArgumentsPass($this->factory);
    }

    /**
     * Test related method
     */
    public function testInstanceOfCompilerPassInterface()
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $this->pass);
    }

    /**
     * Test related method
     */
    public function testReplaceArgumentsWithTaggedEncodersAndNormalizers()
    {
        $serializerDef = $this->getDefinitionMock();
        $container = $this->getContainerBuilderMock(
            $serializerDef,
            array(
                'pim_serializer.normalizer.foo' => array(),
                'pim_serializer.normalizer.bar' => array(),
            ),
            array(
                'pim_serializer.encoder.baz' => array(),
            )
        );

        $this->factory
            ->expects($this->at(0))
            ->method('createReference')
            ->with('pim_serializer.normalizer.foo')
            ->will($this->returnValue($foo = $this->getReferenceMock()));

        $this->factory
            ->expects($this->at(1))
            ->method('createReference')
            ->with('pim_serializer.normalizer.bar')
            ->will($this->returnValue($bar = $this->getReferenceMock()));

        $this->factory
            ->expects($this->at(2))
            ->method('createReference')
            ->with('pim_serializer.encoder.baz')
            ->will($this->returnValue($baz = $this->getReferenceMock()));

        $serializerDef->expects($this->once())
            ->method('setArguments')
            ->with(array(array($foo, $bar), array($baz)));

        $this->pass->process($container);
    }

    /**
     * Test related method
     */
    public function testUnavailableSerializer()
    {
        $container = $this->getContainerBuilderMock();

        $this->pass->process($container);
    }

    /**
     * @param mixed $definition
     * @param array $normalizers
     * @param array $encoders
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private function getContainerBuilderMock(
        $definition = null,
        array $normalizers = array(),
        array $encoders = array()
    ) {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->any())
            ->method('hasDefinition')
            ->with('pim_serializer')
            ->will($this->returnValue(null !== $definition));

        if ($definition) {
            $container->expects($this->any())
                ->method('getDefinition')
                ->will($this->returnValue($definition));

            $container->expects($this->exactly(2))
                ->method('findTaggedServiceIds')
                ->will(
                    $this->returnCallback(
                        function ($tag) use ($encoders, $normalizers) {
                            return ('pim_serializer.encoder' == $tag)
                                ? $encoders
                                : $normalizers;
                        }
                    )
                );
        } else {
            $container->expects($this->never())
                ->method('getDefinition');
        }

        return $container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function getDefinitionMock()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\Definition');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Reference
     */
    private function getReferenceMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\Reference')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
