<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\Sftp;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\JobAutomation\Domain\Model\SftpStorage;
use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\RemoteStorageClientProviderInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV3\ConnectionProvider;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;

final class SftpStorageClientProvider implements RemoteStorageClientProviderInterface
{
    private const MAX_RETRIES = 4;
    private const USE_AGENT = false;
    private const TIMEOUT = 10;

    public function __construct(
        private readonly Encrypter $encrypter,
    ) {
    }

    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        if (!$storage instanceof SftpStorage) {
            throw new \InvalidArgumentException('The provider only support SftpStorage');
        }

        $encryptionKey = $this->getEncryptionKey($storage);
        $password = $storage->getPassword() ? $this->encrypter->decrypt($storage->getPassword(), $encryptionKey) : null;

        $connection = $this->createConnectionProvider(
            loginType: $storage->getLoginType(),
            host: $storage->getHost(),
            username: $storage->getUsername(),
            password: $password,
            privateKey: $storage->getPrivateKey(),
            port: $storage->getPort(),
            hostFingerprint: $storage->getFingerprint(),
        );

        return new FileSystemStorageClient(new Filesystem(new SftpAdapter($connection, '')));
    }

    public function supports(StorageInterface $storage): bool
    {
        return $storage instanceof SftpStorage;
    }

    public function getConnectionProvider(StorageInterface $storage): ConnectionProvider
    {
        if (!$storage instanceof SftpStorage) {
            throw new \InvalidArgumentException('The provider only support SftpStorage');
        }

        return $this->createConnectionProvider(
            loginType: $storage->getLoginType(),
            host: $storage->getHost(),
            username: $storage->getUsername(),
            password: $storage->getPassword(),
            privateKey: $storage->getPrivateKey(),
            port: $storage->getPort(),
            hostFingerprint: $storage->getFingerprint(),
        );
    }

    private function getEncryptionKey(SftpStorage $storage): string
    {
        return sprintf(
            '%s@%s:%s',
            $storage->getUsername(),
            $storage->getHost(),
            $storage->getPort(),
        );
    }

    private function createConnectionProvider(
        string $loginType,
        string $host,
        string $username,
        ?string $password,
        ?string $privateKey,
        int $port,
        ?string $hostFingerprint,
    ): SftpConnectionProvider {
        return match ($loginType) {
            SftpStorage::LOGIN_TYPE_PASSWORD => $this->createConnectionProviderWithPassword($host, $username, $password, $port, $hostFingerprint),
            SftpStorage::LOGIN_TYPE_PRIVATE_KEY => $this->createConnectionProviderWithPrivateKey($host, $username, $privateKey, $port, $hostFingerprint),
            default => throw new \LogicException(sprintf('Unsupported login type "%s"', $loginType)),
        };
    }

    private function createConnectionProviderWithPassword(
        string $host,
        string $username,
        string $password,
        int $port,
        ?string $hostFingerprint,
    ): SftpConnectionProvider {
        return new SftpConnectionProvider(
            host: $host,
            username: $username,
            password: $password,
            privateKey: null,
            passphrase: null,
            port: $port,
            useAgent: self::USE_AGENT,
            timeout: self::TIMEOUT,
            maxTries: self::MAX_RETRIES,
            hostFingerprint: $hostFingerprint,
        );
    }

    private function createConnectionProviderWithPrivateKey(
        string $host,
        string $username,
        string $privateKey,
        int $port,
        ?string $hostFingerprint,
    ): SftpConnectionProvider {
        return new SftpConnectionProvider(
            host: $host,
            username: $username,
            password: null,
            privateKey: $privateKey,
            passphrase: null,
            port: $port,
            useAgent: self::USE_AGENT,
            timeout: self::TIMEOUT,
            maxTries: self::MAX_RETRIES,
            hostFingerprint: $hostFingerprint,
        );
    }
}
