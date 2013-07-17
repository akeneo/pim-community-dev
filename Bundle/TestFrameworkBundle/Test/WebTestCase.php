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
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        /** @var  \Oro\Bundle\TestFrameworkBundle\Test\Client $client */
        $client = parent::createClient($options, $server);

        if (self::$db_isolation && Client::getTransactionLevel() < 1) {
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
        }
        return $client;
    }

    public static function tearDownAfterClass()
    {
        if (self::$db_isolation) {
            Client::rollbackTransaction();
            self::$db_isolation = false;
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

    public function getIsolation()
    {
        return self::$db_isolation;
    }

    public function setIsolation($dbIsolation = false)
    {
        self::$db_isolation = $dbIsolation;
    }
}
