<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\WorkflowBundle\Validator\Constraints\TransitionIsAllowed;
use Oro\Bundle\WorkflowBundle\Validator\Constraints\TransitionIsAllowedValidator;

class TransitionIsAllowedTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $workflowItem = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem');
        $transitionName = 'test_transition';

        $constraint = new TransitionIsAllowed($workflowItem, $transitionName);

        $this->assertEquals($workflowItem, $constraint->getWorkflowItem());
        $this->assertEquals($transitionName, $constraint->getTransitionName());
        $this->assertEquals(TransitionIsAllowedValidator::ALIAS, $constraint->validatedBy());
        $this->assertEquals(TransitionIsAllowed::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
