<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Form\Type;

use Oro\Bundle\DataFlowBundle\Entity\Connector;
use Oro\Bundle\DataFlowBundle\Form\Type\ConnectorType;
use Symfony\Component\Form\FormBuilder;

/**
 * Test related class
 *
 *
 */
class ConnectorTypeTest extends \PHPUnit_Framework_TestCase
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
        $this->formType = new ConnectorType();
    }

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $builder = new FormBuilder('name', null, $dispatcher, $factory);

        $connector = new Connector();
        $options = array('data' => $connector, 'serviceIds' => array('testservice'));
        $this->formType->buildForm($builder, $options);
        $this->assertEquals($this->formType->getName(), 'oro_dataflow_connector');

        $connector->setServiceId('testservice');
        $this->formType->buildForm($builder, $options);
        $this->assertEquals($this->formType->getName(), 'oro_dataflow_connector');
    }
}
