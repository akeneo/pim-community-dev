<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\AttributeAssembler;

class AttributeAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidOptionsDataProvider
     *
     * @param array $configuration
     * @param string $exception
     * @param string $message
     */
    public function testAssembleRequiredOptionException($configuration, $exception, $message)
    {
        $this->setExpectedException($exception, $message);

        $assembler = new AttributeAssembler();
        $assembler->assemble($configuration);
    }

    public function invalidOptionsDataProvider()
    {
        return array(
            'no_options' => array(
                array('name' => array()),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "label" is required'
            ),
            'no_type' => array(
                array('name' => array('label' => 'test')),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "type" is required'
            ),
            'no_label' => array(
                array('name' => array('type' => 'test')),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "label" is required'
            ),
            'invalid_type' => array(
                array('name' => array('label' => 'Label', 'type' => 'text')),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                //@codingStandardsIgnoreStart
                'Invalid attribute type "text", allowed types are "bool", "boolean", "int", "integer", "float", "string", "array", "object", "entity"'
                //@codingStandardsIgnoreEnd
            ),
            'invalid_type_class' => array(
                array(
                    'name' => array(
                        'label'   => 'Label', 'type'    => 'string', 'options' => array('class' => 'stdClass')
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "class" cannot be used in attribute with type "string"'
            ),
            'missing_object_class' => array(
                array('name' => array('label'   => 'Label', 'type'    => 'object')),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "class" is required for attribute with type "object"'
            ),
            'missing_entity_class' => array(
                array('name' => array('label'   => 'Label', 'type'    => 'entity')),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "class" is required for attribute with type "entity"'
            ),
            'invalid_class' => array(
                array(
                    'name' => array(
                        'label'   => 'Label', 'type'    => 'object', 'options' => array('class' => 'InvalidClass')
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Class "InvalidClass" referenced by "class" option not found'
            ),
            'invalid_type_with_managed_entity_option' => array(
                array(
                    'name' => array(
                        'label'   => 'Label', 'type'    => 'object',
                        'options' => array('class' => 'DateTime', 'managed_entity' => true)
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "managed_entity" cannot be used with attribute type "object"'
            ),
            'more_than_one_managed_entity_attributes' => array(
                array(
                    'first_attribute' => array(
                        'label'   => 'First', 'type'    => 'entity',
                        'options' => array('class' => 'stdClass', 'managed_entity' => true)
                    ),
                    'second_attribute' => array(
                        'label'   => 'Second', 'type'    => 'entity',
                        'options' => array('class' => 'stdClass', 'managed_entity' => true)
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'More than one attribute with "managed_entity" option is not allowed'
            ),
        );
    }

    /**
     * @dataProvider configurationDataProvider
     * @param array $configuration
     * @param Attribute $expectedAttribute
     */
    public function testAssemble($configuration, $expectedAttribute)
    {
        $assembler = new AttributeAssembler();
        $attributes = $assembler->assemble($configuration);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attributes);
        $this->assertCount(1, $attributes);
        $this->assertTrue($attributes->containsKey($expectedAttribute->getName()));

        $this->assertEquals($expectedAttribute, $attributes->get($expectedAttribute->getName()));
    }

    public function configurationDataProvider()
    {
        return array(
            'minimal' => array(
                array(
                    'attribute_one' => array('label' => 'label', 'type' => 'string')
                ),
                $this->getAttribute('attribute_one', 'label', 'string', array())
            ),
            'full' => array(
                array(
                    'attribute_two' => array(
                        'label' => 'label', 'type' => 'string', 'options' => array('key' => 'value')
                    )
                ),
                $this->getAttribute('attribute_two', 'label', 'string', array('key' => 'value'))
            )
        );
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $type
     * @param array $options
     * @return Attribute
     */
    protected function getAttribute($name, $label, $type, array $options = array())
    {
        $attribute = new Attribute();
        $attribute->setName($name);
        $attribute->setLabel($label);
        $attribute->setType($type);
        $attribute->setOptions($options);
        return $attribute;
    }
}
