<?php
namespace Pim\Bundle\BatchBundle\Tests\Unit\Connector;

use Pim\Bundle\BatchBundle\Tests\Unit\Connector\Demo\MyConnector;
use Pim\Bundle\BatchBundle\Tests\Unit\Configuration\Demo\MyConfiguration;
use Pim\Bundle\BatchBundle\Tests\Unit\Configuration\Demo\MyOtherConfiguration;

/**
 * Test related class
 *
 *
 */
class ConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MyConnector
     */
    protected $connector;

    /**
     * @var string
     */
    protected $configurationName;

    /**
     * Setup
     */
    public function setup()
    {
        $this->configurationName = 'Pim\Bundle\BatchBundle\Tests\Unit\Configuration\Demo\MyConfiguration';
        $this->connector = new MyConnector($this->configurationName);
    }

    /**
     * Test related method
     */
    public function testConfigure()
    {
        $this->assertNull($this->connector->getConfiguration());
        $this->assertEquals($this->connector->getConfigurationName(), $this->configurationName);
        $configuration = new MyConfiguration();
        $this->connector->configure($configuration);
        $this->assertEquals($this->connector->getConfiguration(), $configuration);
        $this->assertEquals($this->connector->getConfigurationName(), $this->configurationName);
    }

    /**
     * Test related method
     * @expectedException \Pim\Bundle\BatchBundle\Exception\ConfigurationException
     */
    public function testConfigureException()
    {
        $configuration = new MyOtherConfiguration();
        $this->connector->configure($configuration);
    }
}
