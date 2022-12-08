<?php

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Security;

use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EncrypterTest extends KernelTestCase
{
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    public function test_it_encrypts_and_decrypts_a_string()
    {
        $encrypter = new Encrypter('THIS_IS_SECRET');
        $encryptionKey = 'foo@bar:42';
        $string = 'my_password';

        $encryptedString = $encrypter->encrypt($string, $encryptionKey);
        $decryptedString = $encrypter->decrypt($encryptedString, $encryptionKey);

        $this->assertNotSame($string, $encryptedString);
        $this->assertSame($string, $decryptedString);
    }
}
