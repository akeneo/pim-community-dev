<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Configs;
use Pim\Bundle\ConnectorIcecatBundle\Form\Type\ConfigsType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConfigsTypeTest extends KernelAwareTest
{
    /**
     * Test related method
     */
    public function testBuildForm()
    {
        $configManager = $this->container->get('pim.connector_icecat.config_manager');
        $config = $configManager->getConfig();
        $configs = new Configs($config);

        $this->container->get('form.factory')->create(new ConfigsType(), $configs);
    }

}
