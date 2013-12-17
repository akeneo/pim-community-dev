<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ImportExportBundle\Validator\Constraints\JobInstance;

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
            'Failed to create an "job_type" with an unknown job definition',
            $this->constraint->message
        );
    }
}
