<?php

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Security;

use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use PhpSpec\ObjectBehavior;

class AmazonS3CredentialsEncrypterSpec extends ObjectBehavior
{
    public function let(Encrypter $encrypter)
    {
        $key = 'a_region:a_bucket:a_key';

        $encrypter
            ->encrypt('s3cr3t', $key)
            ->willReturn('encrypted_secret');

        $encrypter
            ->decrypt('encrypted_secret', $key)
            ->willReturn('s3cr3t');

        $this->beConstructedWith($encrypter);
    }

    public function it_encrypts_credentials()
    {
        $data = [
            'type' => 'amazon_s3',
            'region' => 'a_region',
            'bucket' => 'a_bucket',
            'key' => 'a_key',
            'secret' => 's3cr3t',
            'file_path' => 'a_filepath',
        ];

        $this->encryptCredentials($data)->shouldReturn([
            'type' => 'amazon_s3',
            'region' => 'a_region',
            'bucket' => 'a_bucket',
            'key' => 'a_key',
            'secret' => 'encrypted_secret',
            'file_path' => 'a_filepath',
        ]);
    }

    public function it_decrypts_credentials()
    {
        $data = [
            'type' => 'amazon_s3',
            'region' => 'a_region',
            'bucket' => 'a_bucket',
            'key' => 'a_key',
            'secret' => 'encrypted_secret',
            'file_path' => 'a_filepath',
        ];

        $this->decryptCredentials($data)->shouldReturn([
            'type' => 'amazon_s3',
            'region' => 'a_region',
            'bucket' => 'a_bucket',
            'key' => 'a_key',
            'secret' => 's3cr3t',
            'file_path' => 'a_filepath',
        ]);
    }

    public function it_supports_data_for_amazon_s3_configuration()
    {
        $data = [
            'type' => 'amazon_s3',
            'region' => 'a_region',
            'bucket' => 'a_bucket',
            'key' => 'a_key',
            'secret' => 's3cr3t',
            'file_path' => 'a_filepath',
        ];

        $noAmazonS3Data = [
            'type' => 'sftp',
            'username' => 'username',
            'host' => 'host',
            'port' => '22',
            'password' => 's3cr3t',
        ];

        $this->support($data)->shouldReturn(true);
        $this->support($noAmazonS3Data)->shouldReturn(false);
    }
}
