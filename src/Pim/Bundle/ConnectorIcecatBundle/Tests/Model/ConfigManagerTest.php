<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Model;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;
use Pim\Bundle\ConnectorIcecatBundle\Model\ConfigManager;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConfigManagerTest extends KernelAwareTest
{
    /**
     * @return ConfigManager
     */
    protected function getManager()
    {
        return $this->container->get('pim.connector_icecat.config_manager');
    }

    /**
     * Assert entity is a Config entity
     * @param object $entity
     */
    protected function assertInstanceOfConfig($entity)
    {
        $this->assertInstanceOf('\Pim\Bundle\ConnectorIcecatBundle\Entity\Config', $entity);
    }

    /**
     * test related method
     */
    public function testGetConfig()
    {
        $configs = $this->getManager()->getConfig();

        $this->assertNotEmpty($configs);
        foreach ($configs as $config) {
            $this->assertInstanceOfConfig($config);
        }
    }

    /**
     * test related method
     */
    public function testGet()
    {
        $config = $this->getManager()->get(Config::BASE_URL);
        $this->assertInstanceOfConfig($config);
    }

    /**
     * test related method
     */
    public function testGetValue()
    {
        $value = $this->getManager()->getValue(Config::BASE_DIR);
//         $this->assertEquals('/tmp/', $value);
    }

    /**
     * test related method
     */
    public function testGetEntityShortname()
    {
        $entityShortName = $this->getManager()->getEntityShortname();
        $this->assertEquals('PimConnectorIcecatBundle:Config', $entityShortName);
    }

    /**
     * Test to recover an unexistent configuration
     * @expectedException \Pim\Bundle\ConnectorIcecatBundle\Exception\ConfigException
     */
    public function testUnexistentConfig()
    {
        $this->getManager()->get('test');
    }
}