<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\User;

class UserApiTest extends \PHPUnit_Framework_TestCase
{
    public function testApi()
    {
        $api  = $this->getApi();
        $user = new User();

        $this->assertEmpty($api->getId());

        $api->setUser($user);

        $this->assertEquals($user, $api->getUser());
    }

    public function testKey()
    {
        $api  = $this->getApi();
        $key  = $api->generateKey();

        $this->assertNotEmpty($key);

        $api->setApiKey($key);

        $this->assertEquals($key, $api->getApiKey());
    }

    protected function setUp()
    {
        $this->api = new UserApi();
    }

    /**
     * @return UserApi
     */
    protected function getApi()
    {
        return $this->api;
    }
}
