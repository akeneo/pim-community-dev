<?php

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Security;

use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use PhpSpec\ObjectBehavior;

class MicrosoftAzureCredentialsEncrypterSpec extends ObjectBehavior
{
    public function let(Encrypter $encrypter)
    {
        $key = 'a_container_name';

        $encrypter
            ->encrypt('c0nnecti0n_string', $key)
            ->willReturn('encrypted_connection_string');

        $encrypter
            ->decrypt('encrypted_connection_string', $key)
            ->willReturn('c0nnecti0n_string');

        $this->beConstructedWith($encrypter);
    }

    public function it_encrypts_credentials()
    {
        $data = [
            'type' => 'microsoft_azure',
            'connection_string' => 'c0nnecti0n_string',
            'container_name' => 'a_container_name',
            'file_path' => 'a_filepath',
        ];

        $this->encryptCredentials($data)->shouldReturn([
            'type' => 'microsoft_azure',
            'connection_string' => 'encrypted_connection_string',
            'container_name' => 'a_container_name',
            'file_path' => 'a_filepath',
        ]);
    }

    public function it_decrypts_credentials()
    {
        $data = [
            'type' => 'microsoft_azure',
            'connection_string' => 'encrypted_connection_string',
            'container_name' => 'a_container_name',
            'file_path' => 'a_filepath',
        ];

        $this->decryptCredentials($data)->shouldReturn([
            'type' => 'microsoft_azure',
            'connection_string' => 'c0nnecti0n_string',
            'container_name' => 'a_container_name',
            'file_path' => 'a_filepath',
        ]);
    }

    public function it_only_supports_data_for_microsoft_azure_configuration()
    {
        $microsoftAzureData = [
            'type' => 'microsoft_azure',
            'connection_string' => 'c0nnecti0n_string',
            'container_name' => 'a_container_name',
            'file_path' => 'a_filepath',
        ];

        $notMicrosoftAzureData = [
            'type' => 'sftp',
            'username' => 'username',
            'host' => 'host',
            'port' => '22',
            'password' => 's3cr3t',
        ];

        $this->support($microsoftAzureData)->shouldReturn(true);
        $this->support($notMicrosoftAzureData)->shouldReturn(false);
    }
}
