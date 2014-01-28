<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\TransformBundle\Transformer\Property\RelationTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RelationTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $entityCache;
    protected $transformer;

    protected $entities;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->entities = array();
        $this->entityCache = $this->getMockBuilder('Pim\Bundle\TransformBundle\Cache\EntityCache')
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityCache
            ->expects($this->any())
            ->method('find')
            ->will($this->returnCallback(array($this, 'findEntity')));
        $this->transformer = new RelationTransformer($this->entityCache);
    }

    /**
     * @param string $class
     * @param string $code
     *
     * @return object|null
     */
    public function findEntity($class, $code)
    {
        return isset($this->entities[$class][$code]) ? $this->entities[$class][$code] : null;
    }

    /**
     * @param string $class
     * @param string $code
     */
    public function addEntity($class, $code)
    {
        if (!isset($this->entities[$class])) {
            $this->entities[$class] = array();
        }
        $this->entities[$class][$code] = new \stdClass();
    }

    /**
     * Test related method
     */
    public function testSingleTransform()
    {
        $this->addEntity('class', 'code');
        $this->assertSame(
            $this->findEntity('class', 'code'),
            $this->transformer->transform(
                ' code ',
                array('class' => 'class')
            )
        );
    }

    public function testEmptyTransform()
    {
        $this->assertNull($this->transformer->transform('', array('class' => 'class')));
        $this->assertEquals(array(), $this->transformer->transform('', array('class' => 'class', 'multiple' => true)));
    }

    /**
     * @expectedException \Pim\Bundle\TransformBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage No entity of class "class" with code "code"
     */
    public function testFailingSingleTransform()
    {
        $this->transformer->transform(
            'code',
            array('class' => 'class')
        );
    }

    /**
     * Test related method
     */
    public function testMultipleTransform()
    {
        $this->addEntity('class', 'code1');
        $this->addEntity('class', 'code2');
        $this->addEntity('class', 'code3');
        $this->assertSame(
            array_values($this->entities['class']),
            $this->transformer->transform(
                ' code1,code2, code3',
                array('class' => 'class', 'multiple' => true)
            )
        );
    }

    /**
     * @expectedException \Pim\Bundle\TransformBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage No entity of class "class" with code "code2"
     */
    public function testFailingMultipleTransform()
    {
        $this->addEntity('class', 'code1');
        $this->transformer->transform(
            ' code1,code2, code3',
            array('class' => 'class', 'multiple' => true)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage class option is required
     */
    public function testNoClass()
    {
        $this->transformer->transform('test');
    }
}
