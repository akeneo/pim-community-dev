<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\JobInstanceValidator;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Symfony\Component\Validator\ExecutionContext $context */
    protected $context;

    /** @var Oro\Bundle\BatchBundle\Connector\ConnectorRegistry $connectorRegistry */
    protected $connectorRegistry;

    /** @var JobInstanceValidator $validator */
    protected $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->context           = $this->getExecutionContextMock();
        $this->connectorRegistry = $this->getConnectorRegistryMock();
        $this->validator         = new JobInstanceValidator($this->connectorRegistry);
        $this->validator->initialize($this->context);
    }

    /**
     * @return \Symfony\Component\Validator\ExecutionContext
     */
    protected function getExecutionContextMock()
    {
        return $this->getMock(
            'Symfony\Component\Validator\ExecutionContext',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return Oro\Bundle\BatchBundle\Connector\ConnectorRegistry
     */
    protected function getConnectorRegistryMock()
    {
        return $this
            ->getMockBuilder('\Oro\Bundle\BatchBundle\Connector\ConnectorRegistry')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test validate method with a valid job instance
     */
    public function testValidJobInstance()
    {
        $this->connectorRegistry
            ->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue(true));

        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(
            new JobInstance(null, null, null),
            new JobInstanceConstraint()
        );
    }

    /**
     * Test validate method with an invalid job instance
     */
    public function testInvalidJobInstance()
    {
        $this->connectorRegistry
            ->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue(null));

        $constraint = new JobInstanceConstraint();
        $this->context
            ->expects($this->once())
            ->method('addViolation')
            ->will($this->returnValue($constraint->message));

        $this->validator->validate(
            new JobInstance(null, null, null),
            new JobInstanceConstraint()
        );
    }
}
