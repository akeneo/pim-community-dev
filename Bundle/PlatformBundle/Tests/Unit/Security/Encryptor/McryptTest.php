<?php

namespace Oro\Bundle\PlatformBundle\Tests\Unit\Security\Encryptor;

use Oro\Bundle\PlatformBundle\Security\Encryptor\Mcrypt;

class McryptTest extends \PHPUnit_Framework_TestCase
{
    const TEST_KEY = 'someKey';

    /** @var Mcrypt */
    protected $encryptor;

    public function setUp()
    {
        $this->encryptor = $this->getInstance();
    }

    public function tearDown()
    {
        unset($this->encryptor);
    }

    /**
     * Test two way encoding/decoding
     */
    public function testEncodeDecode()
    {
        $someData = 'someValue';

        $encrypted = $this->encryptor->encryptData($someData);
        $this->assertInternalType('string', $encrypted);

        $this->assertEquals($someData, $this->encryptor->decryptData($encrypted));
    }

    public function testEncodeDecodeDifferentInstances()
    {
        $someData = 'someValue';

        $encrypted = $this->encryptor->encryptData($someData);

        $newInstance = $this->getInstance();
        $this->assertEquals($someData, $newInstance->decryptData($encrypted));
    }

    /**
     * @return Mcrypt
     */
    protected function getInstance()
    {
        return new Mcrypt(self::TEST_KEY);
    }
}
