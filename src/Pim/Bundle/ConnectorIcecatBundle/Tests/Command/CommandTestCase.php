<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class CommandTestCase extends WebTestCase
{
    /**
     * enter description here
     *
     * @param Client $client  the client
     * @param string $command the command name
     *
     * @return string
     */
    public function runCommand(Client $client, $command)
    {
        $app = new Application($client->getKernel());
        $app->setAutoExit(false);

        $fp = tmpfile();
        $input = new StringInput($command);
        $output = new StreamOutput($fp);

        $app->run($input, $output);

        // TODO : use file_get_content
        fseek($fp, 0);
        $output = '';
        while (!feof($fp)) {
            $output = fread($fp, 4096);
        }
        fclose($fp);

        return $output;
    }
}