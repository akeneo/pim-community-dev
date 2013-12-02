<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\StepAssembler;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowStepType;
use Oro\Bundle\WorkflowBundle\Model\Attribute;

class StepAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formOptionsAssembler;

    /**
     * @var StepAssembler
     */
    protected $assembler;

    protected function setUp()
    {
        $this->formOptionsAssembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\FormOptionsAssembler')
            ->disableOriginalConstructor()
            ->setMethods(array('assemble'))
            ->getMock();

        $this->assembler = new StepAssembler($this->formOptionsAssembler);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @dataProvider invalidOptionsDataProvider
     * @param array $configuration
     */
    public function testAssembleRequiredOptionException($configuration)
    {
        $this->assembler->assemble($configuration, null);
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
     */
    public function testAssemble($configuration, $attributes, Step $expectedStep)
    {
        $configurationPass = $this->getMockBuilder(
            'Oro\Bundle\WorkflowBundle\Model\ConfigurationPass\ConfigurationPassInterface'
        )->getMockForAbstractClass();

        $configurationPass->expects($this->any())
            ->method('passConfiguration')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function (array $data) {
                        if (isset($data['path'])) {
                            $data['path'] = new PropertyPath('data.' . str_replace('$', '', $data['path']));
                        } else {
                            foreach ($data as &$value) {
                                $value = new PropertyPath('data.' . str_replace('$', '', $value));
                            }
                        }
                        return $data;
                    }
                )
            );

        $this->assembler->addConfigurationPass($configurationPass);

        $expectedAttributes = array();
        foreach ($attributes ? $attributes : array() as $attribute) {
            $expectedAttributes[$attribute->getName()] = $attribute;
        }

        $this->formOptionsAssembler->expects($this->once())
            ->method('assemble')
            ->with($this->isType('array'), $expectedAttributes, 'step', $expectedStep->getName())
            ->will($this->returnArgument(0));

        $steps = $this->assembler->assemble($configuration, $attributes);
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
                $this->createStep('step_one')
                    ->setLabel('label')
                    ->setFormType(WorkflowStepType::NAME)
                    ->setOrder(0)
                    ->setIsFinal(false),
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
                                'attribute_one' => array('form_type' => 'text')
                            )
                        ),
                        'view_attributes' => array(
                            array('attribute' => 'attribute_one'),
                            array('path' => '$attribute_one.foo', 'label' => 'Custom Label')
                        )
                    )
                ),
                array(
                    $this->createAttribute('attribute_one')->setLabel('Attribute One'),
                    $this->createAttribute('attribute_two'),
                ),
                $this->createStep('step_two')
                    ->setLabel('label')
                    ->setFormType('custom_workflow_step')
                    ->setTemplate('template')
                    ->setIsFinal(true)
                    ->setOrder(10)
                    ->setAllowedTransitions(array('transition_one'))
                    ->setFormOptions(
                        array(
                            'attribute_fields' => array(
                                'attribute_one' => array('form_type' => 'text')
                            )
                        )
                    )
                    ->setViewAttributes(
                        array(
                            array(
                                'attribute' => 'attribute_one',
                                'path' => new PropertyPath('data.attribute_one'),
                                'label' => 'Attribute One'
                            ),
                            array('path' => new PropertyPath('data.attribute_one.foo'), 'label' => 'Custom Label')
                        )
                    )
            ),
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException
     * @expectedExceptionMessage Unknown attribute "unknown_attribute" at step "step_one"
     */
    public function testUnknownAttributeInViewAttributesException()
    {
        $this->formOptionsAssembler->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(array()));

        $configuration = array(
            'step_one' => array(
                'label' => 'label',
                'view_attributes' => array(
                    array('attribute' => 'unknown_attribute')
                )
            )
        );

        $attributes = array($this->createAttribute('attribute_one'));
        $this->assembler->assemble($configuration, $attributes);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Option "view_attributes" at step "step_one" must be an array
     */
    public function testInvalidViewAttributesOptionException()
    {
        $this->formOptionsAssembler->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(array()));

        $configuration = array(
            'step_one' => array(
                'label' => 'label',
                'view_attributes' => 'string'
            )
        );
        $attributes = array($this->createAttribute('attribute_one'));
        $this->assembler->assemble($configuration, $attributes);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Option "path" or "attribute" at view attribute "0" of step "step_one" is required
     */
    public function testViewAttributeRequiredOptionsException()
    {
        $this->formOptionsAssembler->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(array()));

        $configuration = array(
            'step_one' => array(
                'label' => 'label',
                'view_attributes' => array(
                    array('label' => 'Label')
                )
            )
        );
        $attributes = array($this->createAttribute('attribute_one'));
        $this->assembler->assemble($configuration, $attributes);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Option "label" at view attribute "0" of step "step_one" is required
     */
    public function testViewAttributeRequiredLabelException()
    {
        $this->formOptionsAssembler->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(array()));

        $configuration = array(
            'step_one' => array(
                'label' => 'label',
                'view_attributes' => array(
                    array('path' => '$path')
                )
            )
        );
        $attributes = array($this->createAttribute('attribute_one'));
        $this->assembler->assemble($configuration, $attributes);
    }

    /**
     * @param string $name
     * @return Step
     */
    protected function createStep($name)
    {
        $step = new Step();
        $step->setName($name);

        return $step;
    }

    /**
     * @param string $name
     * @return Attribute
     */
    protected function createAttribute($name)
    {
        $attribute = new Attribute();
        $attribute->setName($name);

        return $attribute;
    }
}
