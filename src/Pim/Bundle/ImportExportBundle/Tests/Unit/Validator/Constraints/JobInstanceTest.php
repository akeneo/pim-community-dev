<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ImportExportBundle\Validator\Constraints\JobInstance;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobInstance
     */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraint = new JobInstance();
    }

    /**
     * Test related method
     */
    public function testMessage()
    {
        $this->assertEquals(
            'Failed to create an {{ job_type }} with an unknown job definition',
            $this->constraint->message
        );
    }

    public function testValidatedBy()
    {
        $this->assertInternalType('string', $this->constraint->validatedBy());
    }

    public function testGetTargets()
    {
        $this->assertEquals(JobInstance::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }
}
