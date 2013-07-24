<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

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

    public function testAssemble()
    {
        $configuration = array(
            'attribute_one' => array(
                'label' => 'label',
                'form_type' => 'form_type'
            ),
            'attribute_two' => array(
                'label' => 'label',
                'form_type' => 'form_type',
                'options' => array('option_one' => 'value')
            ),
        );

        $assembler = new AttributeAssembler();
        $attributes = $assembler->assemble($configuration);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $attributes);
        $this->assertCount(2, $attributes);
        $this->assertTrue($attributes->containsKey('attribute_one'));
        $this->assertTrue($attributes->containsKey('attribute_two'));

        $attributeOne = $attributes->get('attribute_one');
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Model\Attribute', $attributeOne);
        $this->assertEquals($configuration['attribute_one']['label'], $attributeOne->getLabel());
        $this->assertEquals($configuration['attribute_one']['form_type'], $attributeOne->getFormTypeName());
        $this->assertEquals(array(), $attributeOne->getOptions());
        $this->assertEquals('attribute_one', $attributeOne->getName());

        $attributeTwo = $attributes->get('attribute_two');
        $this->assertEquals($configuration['attribute_two']['options'], $attributeTwo->getOptions());
    }
}
