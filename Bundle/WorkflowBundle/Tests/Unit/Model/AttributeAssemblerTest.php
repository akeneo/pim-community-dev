<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\AttributeAssembler;

class AttributeAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\MissedRequiredOptionException
     * @dataProvider invalidOptionsDataProvider
     * @param array $configuration
     */
    public function testAssembleRequiredOptionException($configuration)
    {
        $assembler = new AttributeAssembler();
        $assembler->assemble($configuration);
    }

    public function invalidOptionsDataProvider()
    {
        return array(
            'no options' => array(
                array(
                    'name' => array()
                )
            ),
            'no form_type' => array(
                array(
                    'name' => array(
                        'label' => 'test'
                    )
                )
            ),
            'no label' => array(
                array(
                    'name' => array(
                        'form_type' => 'test'
                    )
                )
            )
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
                    'attribute_one' => array(
                        'label' => 'label',
                        'form_type' => 'form_type'
                    )
                ),
                $this->getAttribute('attribute_one', 'label', 'form_type', array())
            ),
            'full' => array(
                array(
                    'attribute_two' => array(
                        'label' => 'label',
                        'form_type' => 'form_type',
                        'options' => array('key' => 'value')
                    )
                ),
                $this->getAttribute('attribute_two', 'label', 'form_type', array('key' => 'value'))
            )
        );
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $formType
     * @param array $options
     * @return Attribute
     */
    protected function getAttribute($name, $label, $formType, array $options = array())
    {
        $attribute = new Attribute();
        $attribute->setName($name);
        $attribute->setLabel($label);
        $attribute->setFormTypeName($formType);
        $attribute->setOptions($options);
        return $attribute;
    }
}
