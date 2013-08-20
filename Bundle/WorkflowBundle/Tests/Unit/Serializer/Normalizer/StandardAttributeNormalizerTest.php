<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Serializer\Normalizer;

use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Serializer\Normalizer\StandardAttributeNormalizer;

class StandardAttributeNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflow;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    /**
     * @var StandardAttributeNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->workflow = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Workflow')
            ->setMethods(array('getAttribute', 'getName'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->attribute = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')
            ->setMethods(array('getType', 'getOption'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->normalizer = new StandardAttributeNormalizer();
    }

    /**
     * @dataProvider normalizeScalarsAndArrayDataProvider
     *
     * @param string $type
     * @param mixed $value
     * @param mixed $expected
     */
    public function testNormalizeScalarsAndArray($type, $value, $expected)
    {
        $attributeName = 'foo';

        $this->workflow->expects($this->once())->method('getAttribute')->with($attributeName)
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->once())->method('getType')->will($this->returnValue($type));

        $this->assertEquals($expected, $this->normalizer->normalize($this->workflow, $attributeName, $value));
    }

    public function normalizeScalarsAndArrayDataProvider()
    {
        return array(
            'string' => array(
                'type' => 'string',
                'value' => '000',
                'expected' => '000',
            ),
            'string_object' => array(
                'type' => 'string',
                'value' => new \stdClass(),
                'expected' => null,
            ),
            'int' => array(
                'type' => 'int',
                'value' => '01.1',
                'expected' => 1,
            ),
            'integer' => array(
                'type' => 'integer',
                'value' => '-12345.67',
                'expected' => -12345,
            ),
            'bool' => array(
                'type' => 'bool',
                'value' => '',
                'expected' => false,
            ),
            'boolean' => array(
                'type' => 'boolean',
                'value' => 'false',
                'expected' => true,
            ),
            'float' => array(
                'type' => 'float',
                'value' => '-12345.67',
                'expected' => -12345.67,
            ),
            'not_array' => array(
                'type' => 'array',
                'value' => '-12345.67',
                'expected' => array(),
            ),
            'array' => array(
                'type' => 'array',
                'value' => array(1, 2, 3),
                'expected' => array(1, 2, 3),
            ),
        );
    }

    /**
     * @dataProvider normalizeObjectDataProvider
     *
     * @param mixed $value
     * @param mixed $class
     * @param mixed $expected
     */
    public function testNormalizeObject($value, $class, $expected)
    {
        $type = 'object';
        $attributeName = 'foo';

        $this->workflow->expects($this->once())->method('getAttribute')->with($attributeName)
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->once())->method('getType')->will($this->returnValue($type));
        $this->attribute->expects($this->once())
            ->method('getOption')->with('class')
            ->will($this->returnValue($class));

        $this->assertEquals($expected, $this->normalizer->normalize($this->workflow, $attributeName, $value));
    }

    public function normalizeObjectDataProvider()
    {
        return array(
            'not_object' => array(
                'value' => '01.1',
                'class' => 'stdClass',
                'expected' => null,
            ),
            'not_instance_of_class' => array(
                'value' => new \DateTime(),
                'class' => 'stdClass',
                'expected' => null,
            ),
            'object' => array(
                'value' => new \stdClass(),
                'class' => 'stdClass',
                'expected' => new \stdClass(),
            ),
        );
    }

    /**
     * @dataProvider normalizeScalarsAndArrayDataProvider
     *
     * @param string $type
     * @param mixed $value
     * @param mixed $expected
     */
    public function testDenormalizeScalarsAndArray($type, $value, $expected)
    {
        $attributeName = 'foo';

        $this->workflow->expects($this->once())->method('getAttribute')->with($attributeName)
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->once())->method('getType')->will($this->returnValue($type));

        $this->assertEquals($expected, $this->normalizer->denormalize($this->workflow, $attributeName, $value));
    }

    /**
     * @dataProvider normalizeObjectDataProvider
     *
     * @param mixed $value
     * @param mixed $class
     * @param mixed $expected
     */
    public function testDenormalizeObject($value, $class, $expected)
    {
        $type = 'object';
        $attributeName = 'foo';

        $this->workflow->expects($this->once())->method('getAttribute')->with($attributeName)
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->once())->method('getType')->will($this->returnValue($type));
        $this->attribute->expects($this->once())
            ->method('getOption')->with('class')
            ->will($this->returnValue($class));

        $this->assertEquals($expected, $this->normalizer->normalize($this->workflow, $attributeName, $value));
    }

    /**
     * @dataProvider supportsNormalizeAndDenormalizeDataProvider
     *
     * @param string $direction
     * @param string $type
     * @param bool $expected
     */
    public function testSupportsNormalizeAndDenormalize($direction, $type, $expected)
    {
        $attributeName = 'foo';
        $attributeValue = 'bar';

        $this->workflow->expects($this->once())->method('getAttribute')->with($attributeName)
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->once())->method('getType')->will($this->returnValue($type));

        $method = 'supports' . ucfirst($direction);
        $this->assertEquals($expected, $this->normalizer->$method($this->workflow, $attributeName, $attributeValue));
    }

    public function supportsNormalizeAndDenormalizeDataProvider()
    {
        return array(
            array('normalization', 'int', true),
            array('normalization', 'integer', true),
            array('normalization', 'bool', true),
            array('normalization', 'boolean', true),
            array('normalization', 'float', true),
            array('normalization', 'array', true),
            array('normalization', 'object', true),
            array('normalization', 'entity', false),
            array('denormalization', 'int', true),
            array('denormalization', 'integer', true),
            array('denormalization', 'bool', true),
            array('denormalization', 'boolean', true),
            array('denormalization', 'float', true),
            array('denormalization', 'array', true),
            array('denormalization', 'object', true),
            array('denormalization', 'entity', false),
        );
    }

    public function testNotSupportsNormalizeWithoutAttribute()
    {
        $attributeName = 'foo';
        $attributeValue = 'bar';

        $this->workflow->expects($this->once())->method('getAttribute')->with($attributeName);

        $this->assertFalse($this->normalizer->supportsNormalization($this->workflow, $attributeName, $attributeValue));
    }

    public function testNotSupportsDenormalizeWithoutAttribute()
    {
        $attributeName = 'foo';
        $attributeValue = 'bar';

        $this->workflow->expects($this->once())->method('getAttribute')->with($attributeName);

        $this->assertFalse(
            $this->normalizer->supportsDenormalization($this->workflow, $attributeName, $attributeValue)
        );
    }
}
