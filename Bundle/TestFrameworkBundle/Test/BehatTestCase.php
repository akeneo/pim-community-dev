<?php

namespace Oro\Bundle\TestFrameworkBundle\Test;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Behat\Behat\Console\Command\BehatCommand;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

abstract class BehatTestCase extends WebTestCase
{
    /** @var  Application */
    protected static $internalApplication;

    /** @var  BehatCommand */
    public static $internalCommand;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        require_once 'Behat/autoload.php';
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        /** @var Client $client */
        $client = parent::createClient($options, $server);
        $kernel = $client->getKernel();
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false);
        self::$internalApplication  = $application;

        $command = new BehatCommand();
        $command->setApplication($application);
        self::$internalCommand = $command;
        return $client;
    }

    protected function runFeature($path = '', $feature = null, $parameters = array(), $config = 'behat.yml')
    {
        $parameters = array_merge(
            $parameters,
            array('behat', '-f' => 'progress', '-v' => '', '-c' => $path . DIRECTORY_SEPARATOR . $config)
        );

        if (!$feature) {
            $parameters = array_merge($parameters, array('features' => $feature));
        }

        try {
            $input = new ArrayInput($parameters);
            $output = new ConsoleOutput();
            $result = self::$internalCommand->run($input, $output);
            $this->assertEquals(0, $result);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
