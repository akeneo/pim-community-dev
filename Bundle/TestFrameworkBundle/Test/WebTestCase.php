<?php

namespace Oro\Bundle\TestFrameworkBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

class WebTestCase extends BaseWebTestCase
{
    const DB_ISOLATION = '/@db_isolation(.*)(\r|\n)/U';
    const DB_REINDEX = '/@db_reindex(.*)(\r|\n)/U';

    static protected $db_isolation = false;
    static protected $db_reindex = false;

    /**
     * @var Client
     */
    static protected $internalClient;

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

        if (!self::$internalClient) {
            self::$internalClient = parent::createClient($options, $server);

            if (self::$db_isolation) {
                /** @var Client $client */
                $client = self::$internalClient;

                //workaround MyISAM search tables are not on transaction
                if (self::$db_reindex) {
                    $kernel = $client->getKernel();
                    $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
                    $application->setAutoExit(false);
                    $options = array('command' => 'oro:search:reindex');
                    $options['--env'] = "test";
                    $options['--quiet'] = null;
                    $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
                }

                $client->startTransaction();
                $pdoConnection = Client::getPdoConnection();
                if ($pdoConnection) {
                    //set transaction level to 1 for entityManager
                    $connection = $client->createConnection($pdoConnection);
                    $client->getContainer()->set('doctrine.dbal.default_connection', $connection);

                    //set transaction level to 1 for entityManager
                    $connection = $client->getContainer()->get('doctrine.orm.entity_manager')->getConnection();
                    $reflection = new \ReflectionProperty('Doctrine\DBAL\Connection', '_transactionNestingLevel');
                    $reflection->setAccessible(true);
                    $reflection->setValue($connection, 1);
                }
            }
        }

        return self::$internalClient;
    }

    public static function tearDownAfterClass()
    {
        if (self::$internalClient) {
            /** @var Client $client */
            $client = self::$internalClient;
            if (self::$db_isolation) {
                $client->rollbackTransaction();
                self::$db_isolation = false;
            }
            $client->setSoapClient(null);
            self::$internalClient = null;
        }
    }

    public static function setUpBeforeClass()
    {
        $class = new \ReflectionClass(get_called_class());
        $doc = $class->getDocComment();
        if (preg_match(self::DB_ISOLATION, $doc, $matches) > 0) {
            self::$db_isolation = true;
        } else {
            self::$db_isolation = false;
        }

        if (preg_match(self::DB_REINDEX, $doc, $matches) > 0) {
            self::$db_reindex = true;
        } else {
            self::$db_reindex = false;
        }
    }

    /**
     * @return bool
     */
    public function getIsolation()
    {
        return self::$db_isolation;
    }

    /**
     * @param bool $dbIsolation
     */
    public function setIsolation($dbIsolation = false)
    {
        self::$db_isolation = $dbIsolation;
    }
}
