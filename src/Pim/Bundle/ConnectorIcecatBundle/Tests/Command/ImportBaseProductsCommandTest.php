<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Command;

/**
 * Test for commands
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportBaseProductsCommandTest extends CommandTestCase
{
    /**
     * Test launch method
     */
    public function testExecute()
    {

        $client = self::createClient();

        $output = $this->runCommand($client, "connectoricecat:importBaseProducts");
    }
}