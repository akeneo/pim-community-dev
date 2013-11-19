<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\OptionsResolver\Options;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowAttributesType;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;

class WorkflowTransitionTypeTest extends AbstractWorkflowAttributesTypeTestCase
{
    /**
     * @var WorkflowTransitionType
     */
    protected $type;

    protected function setUp()
    {
        parent::setUp();
        $this->type = new WorkflowTransitionType();
    }

    protected function tearDown()
    {
        unset($this->type);
        parent::tearDown();
    }

    protected function getExtensions()
    {
        return array(
            new PreloadedExtension(
                array(
                    WorkflowAttributesType::NAME => new WorkflowAttributesType($this->workflowRegistry),
                ),
                array()
            )
        );
    }

    public function testGetName()
    {
        $this->assertEquals('oro_workflow_transition', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_workflow_attributes', $this->type->getParent());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $resolver->expects($this->once())
            ->method('setRequired')
            ->with(array('workflow_item', 'transition_name'));

        $resolver->expects($this->once())
            ->method('setAllowedTypes')
            ->with(array('transition_name' => 'string'));

        $resolver->expects($this->once())
            ->method('setNormalizers')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        $this->assertInternalType('array', $value);
                        $this->assertArrayHasKey('constraints', $value);
                        $this->assertInternalType('callable', $value['constraints']);

                        $workflowItem = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem');
                        $transitionName = 'test_transition';

                        $options = new Options();

                        $options->set('workflow_item', $workflowItem);
                        $options->set('transition_name', $transitionName);

                        $constraints = array();

                        $constraints = $value['constraints']($options, $constraints);

                        $this->assertInstanceOf(
                            'Oro\Bundle\WorkflowBundle\Validator\Constraints\TransitionIsAllowed',
                            $constraints[0]
                        );

                        $this->assertEquals($workflowItem, $constraints[0]->getWorkflowItem());
                        $this->assertEquals($transitionName, $constraints[0]->getTransitionName());
                    }
                )
            );

        $this->type->setDefaultOptions($resolver);
    }
}
