<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector;

use Oro\Bundle\ImapBundle\Connector\ImapConfig;

class ImapConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $host = 'testHost';
        $port = 'testPort';
        $ssl = 'testSsl';
        $user = 'testUser';
        $password = 'testPwd';
        $obj = new ImapConfig($host, $port, $ssl, $user, $password);

        $this->assertEquals($host, $obj->getHost());
        $this->assertEquals($port, $obj->getPort());
        $this->assertEquals($ssl, $obj->getSsl());
        $this->assertEquals($user, $obj->getUser());
        $this->assertEquals($password, $obj->getPassword());
    }

    public function testSettersAndGetters()
    {
        $obj = new ImapConfig();

        $host = 'testHost';
        $port = 'testPort';
        $ssl = 'testSsl';
        $user = 'testUser';
        $password = 'testPwd';

        $obj->setHost($host);
        $obj->setPort($port);
        $obj->setSsl($ssl);
        $obj->setUser($user);
        $obj->setPassword($password);

        $this->assertEquals($host, $obj->getHost());
        $this->assertEquals($port, $obj->getPort());
        $this->assertEquals($ssl, $obj->getSsl());
        $this->assertEquals($user, $obj->getUser());
        $this->assertEquals($password, $obj->getPassword());
    }
}
