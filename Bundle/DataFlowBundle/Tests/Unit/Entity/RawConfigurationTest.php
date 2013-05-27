<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Entity;

use Oro\Bundle\DataFlowBundle\Entity\RawConfiguration;
use Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo\MyConfiguration;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class RawConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * Setup
     */
    public function setup()
    {
        $this->configuration = new MyConfiguration();
        $this->configuration->setDelimiter('~');
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $rawConfiguration = new RawConfiguration();
        $this->assertNull($rawConfiguration->getId());
    }

    /**
     * Test related method
     */
    public function testGetConfiguration()
    {
        $rawConfiguration = new RawConfiguration($this->configuration);
        $this->assertEquals($rawConfiguration->getConfiguration(), $this->configuration);
        $this->assertEquals($rawConfiguration->getConfiguration(), $this->configuration);
    }

    /**
     * Test related method
     */
    public function testSetConfiguration()
    {
        $rawConfiguration = new RawConfiguration();
        $this->assertNull($rawConfiguration->getConfiguration());
        $rawConfiguration->setConfiguration($this->configuration);
        $this->assertEquals($rawConfiguration->getConfiguration(), $this->configuration);
    }

    /**
     * Test related method
     */
    public function testPostLoad()
    {
        $rawConfiguration = new RawConfiguration($this->configuration);
        $rawConfiguration->preFlush(); // serialize before
        $rawConfiguration->postLoad();
        $this->assertEquals($rawConfiguration->getConfiguration(), $this->configuration);
    }

    /**
     * Test related method
     * @expectedException \Oro\Bundle\DataFlowBundle\Exception\ConfigurationException
     */
    public function testPostLoadException()
    {
        $rawConfiguration = new RawConfiguration();
        $rawConfiguration->postLoad();
    }

    /**
     * Test related method
     */
    public function testPreFlush()
    {
        $rawConfiguration = new RawConfiguration($this->configuration);
        $rawConfiguration->preFlush();
        $this->assertEquals($rawConfiguration->getConfiguration(), $this->configuration);
    }

    /**
     * Test related method
     * @expectedException \Oro\Bundle\DataFlowBundle\Exception\ConfigurationException
     */
    public function testPreFlushException()
    {
        $rawConfiguration = new RawConfiguration();
        $rawConfiguration->preFlush();
    }
}
