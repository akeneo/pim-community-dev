<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\ImportExportBundle\Archiver\JobExecutionArchiver;

/**
 * Job execution archiver test
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionArchiverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobExecutionArchiver $archiver
     */
    protected $archiver;

    /**
     * Setup
     */
    protected function setup()
    {
        $this->archiver = new JobExecutionArchiver('/tmp');
    }

    /**
     * Test related method
     */
    public function testGetBaseDirectory()
    {
        $expected = '/tmp/import/';
        $this->assertEquals($expected, $this->archiver->getBaseDirectory('import'));
    }

    /**
     * Test related method
     */
    public function testGetJobExecutionPath()
    {
        $instance  = new JobInstance('connector', 'import', 'alias');
        $execution = $this->getMock('\Oro\Bundle\BatchBundle\Entity\JobExecution');
        $execution
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(12));
        $execution
            ->expects($this->any())
            ->method('getJobInstance')
            ->will($this->returnValue($instance));
        $expected  = '/tmp/import/alias/12/';
        $this->assertEquals($expected, $this->archiver->getJobExecutionPath($execution));
    }
}
