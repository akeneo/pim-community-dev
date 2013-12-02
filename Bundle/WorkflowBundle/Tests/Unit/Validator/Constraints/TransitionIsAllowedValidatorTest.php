<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Validator\Constraints\TransitionIsAllowedValidator;
use Oro\Bundle\WorkflowBundle\Validator\Constraints\TransitionIsAllowed;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;

class TransitionIsAllowedValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var TransitionIsAllowedValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new TransitionIsAllowedValidator($this->registry);
    }

    /**
     * @dataProvider validateExceptionsDataProvider
     */
    public function testValidateExceptions($workflowException, $expectedViolations)
    {
        $workflowName = 'test_workflow';
        $workflowItem = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem');

        $workflowItem->expects($this->any())
            ->method('getWorkflowName')
            ->will($this->returnValue($workflowName));

        $workflowItem->expects($this->any())
            ->method('getCurrentStepName')
            ->will($this->returnValue('test_step'));

        $transitionName = 'test_transition';
        $constraint = new TransitionIsAllowed($workflowItem, $transitionName);

        $workflow = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Workflow')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowName)
            ->will($this->returnValue($workflow));

        $workflow->expects($this->once())
            ->method('isTransitionAllowed')
            ->with($workflowItem, $transitionName, $this->isInstanceOf('Doctrine\Common\Collections\Collection'), true)
            ->will($this->throwException($workflowException));

        $value = new WorkflowData();

        $context = $this->getMock('Symfony\Component\Validator\ExecutionContextInterface');

        foreach (array_values($expectedViolations) as $index => $expectedViolation) {
            list($message, $params) = array_pad((array)$expectedViolation, 2, array());

            $context->expects($this->at($index))
                ->method('addViolation')
                ->with($message, $params);
        }

        $this->validator->initialize($context);
        $this->validator->validate($value, $constraint);
    }

    public function validateExceptionsDataProvider()
    {
        /** @var TransitionIsAllowed $constraint */
        $constraint = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Validator\Constraints\TransitionIsAllowed')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array(
                'workflowException' => InvalidTransitionException::unknownTransition('test_transition'),
                'expectedViolations' => array(
                    array(
                        $constraint->unknownTransitionMessage,
                        array('{{ transition }}' => 'test_transition')
                    )
                )
            ),
            array(
                'workflowException' => InvalidTransitionException::notStartTransition(
                    'test_workflow',
                    'test_transition'
                ),
                'expectedViolations' => array(
                    array(
                        $constraint->notStartTransitionMessage,
                        array('{{ transition }}' => 'test_transition')
                    )
                )
            ),
            array(
                'workflowException' => InvalidTransitionException::stepHasNoAllowedTransition(
                    'test_workflow',
                    'test_step',
                    'test_transition'
                ),
                'expectedViolations' => array(
                    array(
                        $constraint->stepHasNotAllowedTransitionMessage,
                        array('{{ transition }}' => 'test_transition', '{{ step }}' => 'test_step')
                    )
                )
            ),
            array(
                'workflowException' => new InvalidTransitionException(),
                'expectedViolations' => array(
                    $constraint->someConditionsNotMetMessage
                )
            ),
        );
    }
}
