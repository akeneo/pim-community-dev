<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Service;

use Pim\Bundle\ConnectorIcecatBundle\Service\ConnectorService;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConnectorServiceTest extends KernelAwareTest
{
    /**
     * @return ConnectorService
     */
    protected function getConnectorService()
    {
        return new ConnectorService($this->container);
    }

    /**
     * test related method
     */
    public function testImportCategories()
    {
//         $srvConnector = $this->getConnectorService();
//         $srvConnector->importIcecatCategories();
    }

    /**
     * test related method
     */
    public function testImportSuppliers()
    {
//         $srvConnector = $this->getConnectorService();
//         $srvConnector->importIcecatSuppliers();
    }

    /**
     * test related method
     */
    public function testImportLanguages()
    {
//         $srvConnector = $this->getConnectorService();
//         $srvConnector->importIcecatLanguages();
    }
}
