<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\Sftp;

use Akeneo\Platform\JobAutomation\Domain\Model\Storage\AmazonS3Storage;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\SftpStorage;
use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\Sftp\SftpStorageClient;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use PhpSpec\ObjectBehavior;

class SftpStorageClientProviderSpec extends ObjectBehavior
{
    public function let(
        Encrypter $encrypter,
    )
    {
        $this->beConstructedWith($encrypter);
    }

    public function it_gets_client_from_storage_with_password_login_type(
        Encrypter $encrypter
    ): void {
        $sftpStorage = new SftpStorage(
            'an_host',
            22,
            'password',
            'a_username',
            'a_password',
            'a_file_path',
            null,
            null,
            'a_fingerprint',
        );

        $sftpConnectionProvider = new SftpConnectionProvider(
            'an_host',
            'a_username',
            'a_password',
            null,
            null,
            22,
            false,
            10,
            4,
            'a_fingerprint',
        );

        $filesystemOperator = new Filesystem(
            new SftpAdapter(
                $sftpConnectionProvider,
                '',
            )
        );

        $expectedClient = new SftpStorageClient($filesystemOperator, $sftpConnectionProvider);

        $encrypter->decrypt('a_password', 'a_username@an_host:22')
            ->willReturn('a_password');

        $this->getFromStorage($sftpStorage)->shouldBeLike($expectedClient);
    }

    public function it_gets_client_from_storage_with_private_key_login_type(): void
    {
        $sftpStorage = new SftpStorage(
            'an_host',
            22,
            'private_key',
            'a_username',
            null,
            'a_file_path',
            'a_private_key',
            null,
            'a_fingerprint',
        );

        $expectedSftpConnectionProvider = new SftpConnectionProvider(
            'an_host',
            'a_username',
            null,
            'a_private_key',
            null,
            22,
            false,
            10,
            4,
            'a_fingerprint',
        );

        $expectedFilesystemOperator = new Filesystem(
            new SftpAdapter(
                $expectedSftpConnectionProvider,
                '',
            )
        );

        $expectedClient = new SftpStorageClient($expectedFilesystemOperator, $expectedSftpConnectionProvider);

        $this->getFromStorage($sftpStorage)->shouldBeLike($expectedClient);
    }

    public function it_supports_only_sftp_storage(): void
    {
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
        ))->shouldReturn(true);

        $this->supports(new AmazonS3Storage(
            'a_region',
            'a_bucket',
            'a_key',
            'a_secret',
            'a_file_path',
        ))->shouldReturn(false);
    }
}
