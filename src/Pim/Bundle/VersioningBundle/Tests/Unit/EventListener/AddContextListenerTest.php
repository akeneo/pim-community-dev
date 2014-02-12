<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\EventListener;

use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\VersioningBundle\EventListener\AddContextListener;
use Pim\Bundle\VersioningBundle\Builder\AuditBuilder;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddContextListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $builder  = new AuditBuilder();
        $listener = new AddContextListener($builder);
        $this->assertEquals(
            $listener->getSubscribedEvents(),
            array('oro_batch.before_job_execution' => 'addContext')
        );
    }

    /**
     * Test related method
     */
    public function testAddContext()
    {
        $builder  = new AuditBuilder();
        $listener = new AddContextListener($builder);
        $this->assertEquals($builder->getContext(), '');

        $instance = new JobInstance('connector', 'import', 'alias1');
        $instance->setCode('my code');
        $execution = new JobExecution();
        $execution->setJobInstance($instance);

        $event = new JobExecutionEvent($execution);
        $listener->addContext($event);

        $this->assertEquals($builder->getContext(), 'import "my code"');
    }
}
