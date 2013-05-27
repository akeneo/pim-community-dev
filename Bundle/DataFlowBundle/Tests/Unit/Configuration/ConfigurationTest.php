<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration;

use Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo\MyConfiguration;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MyConfiguration
     */
    protected $configuration;

    /**
     * Setup
     */
    public function setup()
    {
        $this->configuration = new MyConfiguration();
    }

    /**
     * Test related method
     */
    public function testGettersSetters()
    {
        $this->assertEquals($this->configuration->getCharset(), 'UTF-8');
        $this->assertEquals($this->configuration->getDelimiter(), ';');
        $this->assertEquals($this->configuration->getEnclosure(), '"');
        $this->assertEquals($this->configuration->getEscape(), '\\');
        $this->assertNull($this->configuration->getId());
        $this->configuration->setId(42);
        $this->assertEquals($this->configuration->getId(), 42);
    }
}
