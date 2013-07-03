<?php

namespace Oro\Bundle\TestFrameworkBundle\Test;

use Symfony\Bundle\FrameworkBundle\Client as BaseClient;
use Oro\Bundle\TestFrameworkBundle\Test\SoapClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\TerminableInterface;

class Client extends BaseClient
{

    const LOCAL_URL = 'http://localhost/api/rest/latest/';

    public $soapClient;

    /** @var shared doctrine connection */
    static protected $connection = null;

    protected $hasPerformedRequest;

    public function __construct($kernel, array $server = array(), $history = null, $cookieJar = null)
    {
        parent::__construct($kernel, $server, $history, $cookieJar);
        if (is_null(self::$connection)) {
            self::$connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        }
    }

    public function request($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        if (strpos($uri, 'http://') === false) {
            $uri = self::LOCAL_URL . $uri;
        }
        return parent::request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
    }
    /**
     * @param null $wsdl
     * @param array $options
     * @throws \Exception
     */
    public function soap($wsdl = null, array $options = null)
    {
        if (is_null($wsdl)) {
            throw new \Exception('wsdl should not be NULL');
        }

        $this->request('GET', $wsdl);
        $status = $this->getResponse()->getStatusCode();
        $statusText = Response::$statusTexts[$status];
        if ($status >= 400) {
            throw new \Exception($statusText, $status);
        }

        $wsdl = $this->getResponse()->getContent();
        //save to file
        $file=tempnam(sys_get_temp_dir(), date("Ymd") . '_') . '.xml';
        $fl = fopen($file, "w");
        fwrite($fl, $wsdl);
        fclose($fl);

        $this->soapClient = new SoapClient($file, $options, $this);

        unlink($file);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doRequest($request)
    {
        if ($this->hasPerformedRequest) {
            $this->kernel->shutdown();
            $this->kernel->boot();
        } else {
            $this->hasPerformedRequest = true;
        }

        if (!is_null(self::$connection)) {
            $this->getContainer()->set('doctrine.dbal.default_connection', self::$connection);
        }

        $response = $this->kernel->handle($request);

        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }
        return $response;
    }

    /**
     * @param $folder
     */
    public function appendFixtures($folder)
    {
        $loader = new \Doctrine\Common\DataFixtures\Loader;
        $fixtures = $loader->loadFromDirectory($folder);
        foreach ($fixtures as $fixture) {
            $fixture->setContainer($this->getContainer());
        }
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->getContainer()->get('doctrine.orm.entity_manager'));
        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor(
            $this->getContainer()->get('doctrine.orm.entity_manager'),
            $purger
        );
        $executor->execute($loader->getFixtures(), true);
    }

    public function startTransaction()
    {
        self::$connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        if (self::$connection->getTransactionNestingLevel()<1) {
            self::$connection->beginTransaction();
        }
    }

    public static function getTransactionLevel()
    {
        return self::$connection->getTransactionNestingLevel();
    }

    public static function rollbackTransaction()
    {
        if (!is_null(self::$connection)) {
            self::$connection->rollback();
            self::$connection = null;
        }
    }
}
