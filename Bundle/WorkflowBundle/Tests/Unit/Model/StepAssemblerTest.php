<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\StepAssembler;

class StepAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\MissedRequiredOptionException
     * @dataProvider invalidOptionsDataProvider
     * @param array $configuration
     */
    public function testAssembleRequiredOptionException($configuration)
    {
        $assembler = new StepAssembler();
        $assembler->assemble($configuration, null);
    }

    public function invalidOptionsDataProvider()
    {
        return array(
            'no options' => array(
                array(
                    'name' => array()
                )
            ),
            'no label' => array(
                array(
                    'name' => array(
                        'template' => 'test'
                    )
                )
            )
        );
    }

    /**
     * @dataProvider configurationDataProvider
     * @param array $configuration
     * @param array $attributes
     * @param Step $expectedStep
     */
    public function testAssemble($configuration, $attributes, $expectedStep)
    {
        if (array_key_exists('attributes', $configuration[$expectedStep->getName()])) {
            $stepAttributes = new ArrayCollection();
            foreach ($configuration[$expectedStep->getName()]['attributes'] as $attributeName) {
                $stepAttributes->set($attributeName, $attributes[$attributeName]);
            }
            $expectedStep->setAttributes($stepAttributes);
        }

        $assembler = new StepAssembler();
        $steps = $assembler->assemble($configuration, $attributes);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $steps);
        $this->assertCount(1, $steps);
        $this->assertTrue($steps->containsKey($expectedStep->getName()));

        $this->assertEquals($expectedStep, $steps->get($expectedStep->getName()));
    }

    public function configurationDataProvider()
    {
        return array(
            'minimal' => array(
                array(
                    'step_one' => array(
                        'label' => 'label',
                    )
                ),
                null,
                $this->getStep('step_one', 'label', null, 0, false, array())
            ),
            'full' => array(
                array(
                    'step_two' => array(
                        'label' => 'label',
                        'template' => 'template',
                        'order' => 10,
                        'is_final' => true,
                        'allowed_transitions' => array('transition_one'),
                        'attributes' => array('attribute_one')
                    )
                ),
                array('attribute_one' => $this->getAttribute(), 'attribute_two' => $this->getAttribute()),
                $this->getStep(
                    'step_two',
                    'label',
                    'template',
                    10,
                    true,
                    array('transition_one')
                )
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException
     * @expectedExceptionMessage Unknown attribute unknown
     */
    public function testUnknownAttributeException()
    {
        $configuration = array(
            'step_one' => array(
                'label' => 'label',
                'attributes' => array('unknown')
            )
        );
        $attributes = array('attribute_one' => $this->getAttribute());
        $assembler = new StepAssembler();
        $assembler->assemble($configuration, $attributes);
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $template
     * @param int $order
     * @param bool $isFinal
     * @param array $transitions
     * @return Step
     */
    protected function getStep($name, $label, $template, $order, $isFinal, $transitions)
    {
        $step = new Step();
        $step->setName($name);
        $step->setLabel($label);
        $step->setTemplate($template);
        $step->setOrder($order);
        $step->setIsFinal($isFinal);
        $step->setAllowedTransitions($transitions);

        return $step;
    }

    protected function getAttribute()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')
            ->getMock();
    }
}
