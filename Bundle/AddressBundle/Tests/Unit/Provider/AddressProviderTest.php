<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Provider;

use Oro\Bundle\AddressBundle\Provider\AddressProvider;

class AddressProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressProvider
     */
    private $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storageMock;

    /**
     * Environment setup
     */
    public function setUp()
    {
        $this->provider = new AddressProvider();
        $this->storageMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Manager\StorageInterface');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyAliasException()
    {
        $this->provider->addStorage($this->storageMock, '');
    }

    public function testGetStorageResultNull()
    {
        $this->provider->addStorage($this->storageMock, 'test');

        $this->assertNull($this->provider->getStorage('not_exists_one'));
    }

    public function testGetStorageResult()
    {
        $this->provider->addStorage($this->storageMock, 'test');

        $this->assertEquals($this->storageMock, $this->provider->getStorage('test'));
    }
}
