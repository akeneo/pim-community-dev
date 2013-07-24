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
        $this->markTestIncomplete('Incomplete');
//        $assembler = new AttributeAssembler();
//        $assembler->assemble($configuration);
    }
}
