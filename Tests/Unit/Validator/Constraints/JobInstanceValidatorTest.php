<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Validator\Constraints\ExistingJob;
use Oro\Bundle\BatchBundle\Validator\Constraints\ExistingJobValidator;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingJobValidatorTest extends \PHPUnit_Framework_TestCase
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
        $this->validator         = new ExistingJobValidator($this->connectorRegistry);
        $this->validator->initialize($this->context);
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
            new ExistingJob()
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

        $constraint = new ExistingJob();
        $this->context
            ->expects($this->once())
            ->method('addViolation')
            ->with($constraint->message);

        $this->validator->validate(
            new JobInstance(null, null, null),
            new ExistingJob()
        );
    }

    /**
     * @return \Symfony\Component\Validator\ExecutionContext
     */
    protected function getExecutionContextMock()
    {
        return $this->getMock(
            'Symfony\Component\Validator\ExecutionContext',
            array(),
            array(),
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
}
