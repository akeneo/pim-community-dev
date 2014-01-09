<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\BatchBundle\Validator\Constraints\ExistingJob;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingJobTest extends \PHPUnit_Framework_TestCase
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
        $this->constraint = new ExistingJob();
    }

    /**
     * Test related method
     */
    public function testMessage()
    {
        $this->assertEquals(
            'Failed to create an "{{ job_type }}" with an unknown job definition',
            $this->constraint->message
        );
    }
}
