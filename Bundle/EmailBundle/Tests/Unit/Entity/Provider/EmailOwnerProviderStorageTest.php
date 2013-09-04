<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity\Provider;

use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderStorage;

class EmailOwnerProviderStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testStorage()
    {
        $provider1 = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider2 = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');

        $storage = new EmailOwnerProviderStorage();
        $storage->addProvider($provider1);
        $storage->addProvider($provider2);

        $result = $storage->getProviders();

        $this->assertCount(2, $result);
        $this->assertTrue($provider1 === $result[0]);
        $this->assertTrue($provider2 === $result[1]);
    }
}
