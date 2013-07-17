<?php
namespace Oro\Bundle\TestFrameworkBundle\Test;

use \SoapClient as BasicSoapClient;

class SoapClient extends BasicSoapClient
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    protected $kernel;

    /**
     * Overridden constructor
     *
     * @param string $wsdl
     * @param array $options
     * @param \Symfony\Bundle\FrameworkBundle\Client $client
     */
    public function __construct($wsdl, $options, &$client)
    {
        $this->kernel =  $client;
        parent::__construct($wsdl, $options);

    }

    public function __destruct()
    {
        unset($this->kernel);
    }

    /**
     * Overridden _doRequest method
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way
     *
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        //save directly in _SERVER array
        $_SERVER['HTTP_SOAPACTION'] = $action;
        $_SERVER['CONTENT_TYPE'] = 'application/soap+xml';
        //make POST request
        $this->kernel->request('POST', (string)$location, array(), array(), array(), (string)$request);
        unset($_SERVER['HTTP_SOAPACTION']);
        unset($_SERVER['CONTENT_TYPE']);
        return $this->kernel->getResponse()->getContent();
    }
}
