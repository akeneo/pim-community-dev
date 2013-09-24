<?php

namespace Oro\Bundle\OrganizationBundle\Tests\Unit\Validator\Constrains;

use Oro\Bundle\OrganizationBundle\Validator\Constraints\BusinessUnitOwnerValidator;
use Oro\Bundle\OrganizationBundle\Validator\Constraints\BusinessUnitOwner;
use Oro\Bundle\OrganizationBundle\Tests\Unit\Fixtture\Entity\BusinessUnit;

class BusinessUnitOwnerValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BusinessUnitOwnerValidator
     */
    protected $businessUnitOwnerValidator;

    protected $constraint;

    protected $context;

    /**
     * @var BusinessUnit
     */
    protected $businessUnit;

    protected function setUp()
    {
        $this->businessUnit = new BusinessUnit();

        $this->constraint = new BusinessUnitOwner();
        $this->context = $this->getMockForAbstractClass('Symfony\Component\Validator\ExecutionContextInterface');

        $this->businessUnitOwnerValidator = new BusinessUnitOwnerValidator();
        $this->businessUnitOwnerValidator->initialize($this->context);
    }

    public function testValidBusinessUnit()
    {
        $this->businessUnit->setId(1);
        $parentBusinessUnit = new BusinessUnit();
        $parentBusinessUnit->setId(2);
        $this->businessUnit->setOwner($parentBusinessUnit);

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->businessUnitOwnerValidator->validate($this->businessUnit, $this->constraint);
    }

    public function testUnValidBusinessUnit()
    {
        $this->businessUnit->setId(1);
        $this->businessUnit->setOwner($this->businessUnit);

        $this->context->expects($this->once())
            ->method('addViolation');

        $this->businessUnitOwnerValidator->validate($this->businessUnit, $this->constraint);
    }
}
