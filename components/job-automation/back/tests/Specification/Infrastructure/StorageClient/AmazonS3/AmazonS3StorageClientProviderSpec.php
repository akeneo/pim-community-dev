<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\AmazonS3;

use Akeneo\Platform\JobAutomation\Domain\Model\Storage\AmazonS3Storage;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\SftpStorage;
use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\AmazonS3\AmazonS3StorageClient;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;

class AmazonS3StorageClientProviderSpec extends ObjectBehavior
{
    public function let(
        Encrypter $encrypter,
    )
    {
        $this->beConstructedWith($encrypter);
    }

    public function it_gets_client_from_storage(
        Encrypter $encrypter
    ): void {
        $amazonS3Storage = new AmazonS3Storage(
            'eu-west-3',
            'a_bucket',
            'a_key',
            'a_secret',
            'a_file_path',
        );

        $encrypter->decrypt('a_secret', 'eu-west-3:a_bucket:a_key')
            ->willReturn('a_secret');

        $this->getFromStorage($amazonS3Storage)->shouldBeAnInstanceOf(AmazonS3StorageClient::class);
    }

    public function it_supports_only_amazon_s3_storage(): void
    {
        $this->supports(new AmazonS3Storage(
            'eu-west-3',
            'a_bucket',
            'a_key',
            'a_secret',
            'a_file_path',
        ))->shouldReturn(true);

        $this->supports(new SftpStorage(
            'an_host',
            22,
            'a_login_type',
            'a_username',
            null,
            'a_file_path',
            null,
            null,
            null,
        ))->shouldReturn(false);
    }
}
