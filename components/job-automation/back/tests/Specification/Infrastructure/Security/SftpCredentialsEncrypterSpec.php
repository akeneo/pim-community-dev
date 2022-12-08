<?php

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Security;

use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use PhpSpec\ObjectBehavior;

class SftpCredentialsEncrypterSpec extends ObjectBehavior
{
    public function let(Encrypter $encrypter)
    {
        $encrypter
            ->encrypt('s3cr3t', 'username@host:22')
            ->willReturn('encrypted_password');

        $encrypter
            ->decrypt('encrypted_password', 'username@host:22')
            ->willReturn('s3cr3t');

        $this->beConstructedWith($encrypter);
    }

    public function it_encrypts_credentials()
    {
        $data = [
            'type' => 'sftp',
            'username' => 'username',
            'host' => 'host',
            'port' => '22',
            'password' => 's3cr3t',
        ];

        $this->encryptCredentials($data)->shouldReturn([
            'type' => 'sftp',
            'username' => 'username',
            'host' => 'host',
            'port' => '22',
            'password' => 'encrypted_password',
        ]);
    }

    public function it_decrypts_credentials()
    {
        $data = [
            'type' => 'sftp',
            'username' => 'username',
            'host' => 'host',
            'port' => '22',
            'password' => 'encrypted_password',
        ];

        $this->decryptCredentials($data)->shouldReturn([
            'type' => 'sftp',
            'username' => 'username',
            'host' => 'host',
            'port' => '22',
            'password' => 's3cr3t',
        ]);
    }

    public function it_supports_data_for_sftp_configuration()
    {
        $data = [
            'type' => 'sftp',
            'username' => 'username',
            'host' => 'host',
            'port' => '22',
            'password' => 's3cr3t',
        ];

        $noSftpData = [
            'type' => 'aws',
            'token' => 'A_TOKEN',
            'secret' => 'A_SECRET',
        ];

        $this->support($data)->shouldReturn(true);
        $this->support($noSftpData)->shouldReturn(false);
    }
}
