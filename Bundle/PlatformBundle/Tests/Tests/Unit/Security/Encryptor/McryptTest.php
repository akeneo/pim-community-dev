<?php

namespace Oro\Bundle\PlatformBundle\Tests\Unit\Security\Encryptor;

use Oro\Bundle\PlatformBundle\Security\Encryptor\Mcrypt;

class McryptTest extends \PHPUnit_Framework_TestCase
{
    /** @var Mcrypt */
    protected $encryptor;

    public function setUp()
    {
        $this->encryptor = new Mcrypt('someKey');
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
}
