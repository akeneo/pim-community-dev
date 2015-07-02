<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer;

use Pim\Bundle\TransformBundle\Transformer\EntityTransformerRegistry;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTransformerRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    protected $registry;

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->registry = new EntityTransformerRegistry($this->container, 'default_transformer');
    }

    public function getTestData()
    {
        return [
            'default_transformer' => [false],
            'custom_transformer'  => [true],
        ];
    }

    /**
     * @dataProvider getTestData
     */
    public function testRegistry($withCustomTransformer)
    {
        if ($withCustomTransformer) {
            $this->registry->addEntityTransformer('class', 'custom_transformer');
        }

        $transformer = $this->getMock('Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface');
        $transformer->expects($this->any())
            ->method('transform')
            ->with(
                $this->equalTo('class'),
                $this->equalTo(['data']),
                $this->equalTo(['defaults'])
            )
            ->will($this->returnValue('transformed_data'));
        $transformer->expects($this->any())
            ->method('getErrors')
            ->with($this->equalTo('class'))
            ->will($this->returnValue('errors'));
        $transformer->expects($this->any())
            ->method('getTransformedColumnsInfo')
            ->with($this->equalTo('class'))
            ->will($this->returnValue('columns'));

        $this->container->expects($this->any())
            ->method('get')
            ->with($this->equalTo($withCustomTransformer ? 'custom_transformer' : 'default_transformer'))
            ->will($this->returnValue($transformer));

        $this->assertEquals('transformed_data', $this->registry->transform('class', ['data'], ['defaults']));
        $this->assertEquals('errors', $this->registry->getErrors('class'));
        $this->assertEquals('columns', $this->registry->getTransformedColumnsInfo('class'));
    }
}
