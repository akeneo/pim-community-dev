<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\StepAssembler;
use Oro\Bundle\WorkflowBundle\Form\Type\OroWorkflowStep;
use Oro\Bundle\WorkflowBundle\Model\Attribute;

class StepAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
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
        if (array_key_exists('form_options', $configuration[$expectedStep->getName()])) {
            $expectedFormOptions = array('attribute_fields' => array());

            $actualFormOptions = $configuration[$expectedStep->getName()]['form_options'];
            $this->assertArrayHasKey('attribute_fields', $actualFormOptions);
            $attributeFields = $actualFormOptions['attribute_fields'];

            foreach ($attributeFields as $attributeName => $attributeOptions) {
                $expectedFormOptions['attribute_fields'][$attributeName] = $attributeOptions;
            }
            $expectedStep->setFormOptions($expectedFormOptions);
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
                        'form_type' => 'custom_workflow_step',
                        'form_options' => array(
                            'attribute_fields' => array(
                                'attribute_one' => array('form_type' => 'text'),
                                'attribute_two' => array('form_type' => 'text'),
                            )
                        )
                    )
                ),
                array(
                    $this->getAttribute('attribute_one'),
                    $this->getAttribute('attribute_two'),
                ),
                $this->getStep(
                    'step_two',
                    'label',
                    'template',
                    10,
                    true,
                    array('transition_one'),
                    'custom_workflow_step'
                )
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException
     * @expectedExceptionMessage Unknown attribute "unknown_attribute" at step "step_one"
     */
    public function testUnknownAttributeException()
    {
        $configuration = array(
            'step_one' => array(
                'label' => 'label',
                'form_options' => array(
                    'attribute_fields' => array(
                        'unknown_attribute' => array()
                    )
                )
            )
        );
        $attributes = array($this->getAttribute('attribute_one'));
        $assembler = new StepAssembler();
        $assembler->assemble($configuration, $attributes);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Option "attribute_fields" at step "step_one" must be an array
     */
    public function testInvalidAttributeException()
    {
        $configuration = array(
            'step_one' => array(
                'label' => 'label',
                'form_options' => array(
                    'attribute_fields' => 'string'
                )
            )
        );
        $attributes = array($this->getAttribute('attribute_one'));
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
     * @param string $formType
     * @param array $formOptions
     * @return Step
     */
    protected function getStep(
        $name,
        $label,
        $template,
        $order,
        $isFinal,
        array $transitions,
        $formType = OroWorkflowStep::NAME,
        array $formOptions = array()
    ) {
        $step = new Step();
        $step->setName($name);
        $step->setLabel($label);
        $step->setTemplate($template);
        $step->setOrder($order);
        $step->setIsFinal($isFinal);
        $step->setAllowedTransitions($transitions);
        $step->setFormType($formType);
        $step->setFormOptions($formOptions);

        return $step;
    }

    /**
     * @param string $name
     * @return Attribute
     */
    protected function getAttribute($name)
    {
        $attribute = new Attribute();
        $attribute->setName($name);

        return $attribute;
    }
}
