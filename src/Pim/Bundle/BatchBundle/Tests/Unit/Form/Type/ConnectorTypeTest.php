<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Form\Type;

use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\BatchBundle\Form\Type\JobType;
use Symfony\Component\Form\FormBuilder;

/**
 * Test related class
 */
class JobTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ConnectorType
     */
    protected $formType;

    /**
     * Setup
     */
    public function setup()
    {
        $this->formType = new JobType();
    }

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $builder = new FormBuilder('name', null, $dispatcher, $factory);

        $job = new Job();
        $options = array('data' => $job, 'serviceIds' => array('testservice'));
        $this->formType->buildForm($builder, $options);
        $this->assertEquals($this->formType->getName(), 'pim_batch_job');

        $job->setServiceId('testservice');
        $this->formType->buildForm($builder, $options);
        $this->assertEquals($this->formType->getName(), 'pim_batch_job');
    }
}
