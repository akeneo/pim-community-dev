<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use Oro\Bundle\EmailBundle\Validator\Constraints\VariablesConstraint;

class VariableConstraintTest extends \PHPUnit_Framework_TestCase
{
    /** @var VariablesConstraint */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = new VariablesConstraint();
    }

    public function tearDown()
    {
        unset($this->constraint);
    }

    public function testConfiguration()
    {
        $this->assertEquals('oro_email.variables_validator', $this->constraint->validatedBy());
        $this->assertEquals(Constraint::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }
}
