<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypter;
use PhpSpec\ObjectBehavior;

class CredentialsEncrypterRegistrySpec extends ObjectBehavior
{
    private const CLEAR_DATA = [
        'configuration' => [
            'storage' => [
                'type' => 'sftp',
                'username' => 'username',
                'host' => 'host',
                'port' => '22',
                'password' => 's3cr3t',
            ],
        ],
    ];

    private const ENCRYPTED_DATA = [
        'configuration' => [
            'storage' => [
                'type' => 'sftp',
                'username' => 'username',
                'host' => 'host',
                'port' => '22',
                'password' => 'encrypted_password',
            ],
        ],
    ];

    public function let(
        CredentialsEncrypter $encrypter,
    ) {
        $this->beConstructedWith([]);
        $encrypter->support(self::CLEAR_DATA)->willReturn(true);
        $encrypter->support(self::ENCRYPTED_DATA)->willReturn(true);

        $encrypter->encryptCredentials(self::CLEAR_DATA)->willReturn(self::ENCRYPTED_DATA);
        $encrypter->decryptCredentials(self::ENCRYPTED_DATA)->willReturn(self::CLEAR_DATA);
    }

    public function it_encrypts_credentials(
        CredentialsEncrypter $encrypter,
    ) {
        $this->beConstructedWith([$encrypter]);
        $this->encryptCredentials(self::CLEAR_DATA)->shouldReturn(self::ENCRYPTED_DATA);
    }

    public function it_decrypts_credentials(
        CredentialsEncrypter $encrypter,
    ) {
        $this->beConstructedWith([$encrypter]);
        $this->decryptCredentials(self::ENCRYPTED_DATA)->shouldReturn(self::CLEAR_DATA);
    }

    public function it_returns_data_as_it_is_if_no_encrypter_supports_it()
    {
        $this->encryptCredentials(self::CLEAR_DATA)->shouldReturn(self::CLEAR_DATA);
        $this->decryptCredentials(self::ENCRYPTED_DATA)->shouldReturn(self::ENCRYPTED_DATA);
    }
}
