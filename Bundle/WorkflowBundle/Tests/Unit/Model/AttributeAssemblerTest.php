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
                        'label' => 'Label', 'type' => 'string', 'options' => array('class' => 'stdClass')
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "class" cannot be used in attribute "name"'
            ),
            'missing_object_class' => array(
                array('name' => array('label' => 'Label', 'type' => 'object')),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "class" is required in attribute "name"'
            ),
            'missing_entity_class' => array(
                array('name' => array('label' => 'Label', 'type' => 'entity')),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "class" is required in attribute "name"'
            ),
            'invalid_class' => array(
                array(
                    'name' => array(
                        'label' => 'Label', 'type' => 'object', 'options' => array('class' => 'InvalidClass')
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Class "InvalidClass" referenced by "class" option in attribute "name" not found'
            ),
            'object_managed_entity' => array(
                array(
                    'name' => array(
                        'label' => 'Label', 'type' => 'object',
                        'options' => array('class' => 'DateTime', 'managed_entity' => true)
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "managed_entity" cannot be used in attribute "name"'
            ),
            'object_multiple' => array(
                array(
                    'name' => array(
                        'label' => 'Label', 'type' => 'object',
                        'options' => array('class' => 'DateTime', 'multiple' => true)
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "multiple" cannot be used in attribute "name"'
            ),
            'object_bind' => array(
                array(
                    'name' => array(
                        'label' => 'Label', 'type' => 'object',
                        'options' => array('class' => 'DateTime', 'bind' => true)
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Option "bind" cannot be used in attribute "name"'
            ),
            'entity_bind_and_multiple_false' => array(
                array(
                    'name' => array(
                        'label' => 'Label', 'type' => 'entity',
                        'options' => array('class' => 'DateTime', 'bind' => false, 'multiple' => false)
                    )
                ),
                'Oro\Bundle\WorkflowBundle\Exception\AssemblerException',
                'Options "multiple" and "bind" in attribute "name" cannot be false simultaneously'
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

    /**
     * @return array
     */
    public function configurationDataProvider()
    {
        return array(
            'string' => array(
                array('attribute_one' => array('label' => 'label', 'type' => 'string')),
                $this->getAttribute('attribute_one', 'label', 'string')
            ),
            'bool' => array(
                array('attribute_one' => array('label' => 'label', 'type' => 'bool')),
                $this->getAttribute('attribute_one', 'label', 'bool')
            ),
            'boolean' => array(
                array('attribute_one' => array('label' => 'label', 'type' => 'boolean')),
                $this->getAttribute('attribute_one', 'label', 'boolean')
            ),
            'int' => array(
                array('attribute_one' => array('label' => 'label', 'type' => 'int')),
                $this->getAttribute('attribute_one', 'label', 'int')
            ),
            'integer' => array(
                array('attribute_one' => array('label' => 'label', 'type' => 'integer')),
                $this->getAttribute('attribute_one', 'label', 'integer')
            ),
            'float' => array(
                array('attribute_one' => array('label' => 'label', 'type' => 'float')),
                $this->getAttribute('attribute_one', 'label', 'float')
            ),
            'array' => array(
                array('attribute_one' => array('label' => 'label', 'type' => 'array')
                ),
                $this->getAttribute('attribute_one', 'label', 'array')
            ),
            'object' => array(
                array(
                    'attribute_one' => array(
                        'label' => 'label', 'type' => 'object', 'options' => array('class' => 'stdClass')
                    )
                ),
                $this->getAttribute('attribute_one', 'label', 'object', array('class' => 'stdClass'))
            ),
            'entity_minimal' => array(
                array(
                    'attribute_one' => array(
                        'label' => 'label', 'type' => 'entity', 'options' => array('class' => 'stdClass')
                    )
                ),
                $this->getAttribute(
                    'attribute_one',
                    'label',
                    'entity',
                    array('class' => 'stdClass', 'multiple' => false, 'bind' => true)
                )
            ),
            'entity_full' => array(
                array(
                    'attribute_one' => array(
                        'label' => 'label', 'type' => 'entity',
                        'options' => array('class' => 'stdClass', 'multiple' => true, 'bind' => false)
                    )
                ),
                $this->getAttribute(
                    'attribute_one',
                    'label',
                    'entity',
                    array('class' => 'stdClass', 'multiple' => true, 'bind' => false)
                )
            ),
            'entity_multiple_and_bind' => array(
                array(
                    'attribute_one' => array(
                        'label' => 'label', 'type' => 'entity',
                        'options' => array('class' => 'stdClass', 'multiple' => true, 'bind' => true)
                    )
                ),
                $this->getAttribute(
                    'attribute_one',
                    'label',
                    'entity',
                    array('class' => 'stdClass', 'multiple' => true, 'bind' => true)
                )
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
