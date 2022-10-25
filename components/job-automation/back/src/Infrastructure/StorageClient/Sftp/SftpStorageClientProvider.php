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
        private Encrypter $encrypter,
    ) {
    }

    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        if (!$storage instanceof SftpStorage) {
            throw new \InvalidArgumentException('The provider only support SftpStorage');
        }

        $encryptionKey = $this->getEncryptionKey($storage);

        $connection = $this->getConnection(
            loginType: $storage->getLoginType(),
            host: $storage->getHost(),
            username: $storage->getUsername(),
            password: $this->encrypter->decrypt($storage->getPassword(), $encryptionKey),
            privateKey: $storage->getPrivateKey(),
            port: $storage->getPort(),
            useAgent: self::USE_AGENT,
            timeout: self::TIMEOUT,
            maxTries: self::MAX_RETRIES,
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

        return $this->getConnection(
            loginType: $storage->getLoginType(),
            host: $storage->getHost(),
            username: $storage->getUsername(),
            password: $storage->getPassword(),
            privateKey: $storage->getPrivateKey(),
            port: $storage->getPort(),
            useAgent: self::USE_AGENT,
            timeout: self::TIMEOUT,
            maxTries: self::MAX_RETRIES,
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

    private function getConnection(
        string $loginType,
        string $host,
        string $username,
        ?string $password,
        ?string $privateKey,
        int $port,
        bool $useAgent,
        int $timeout,
        int $maxTries,
        string $hostFingerprint,
    ): SftpConnectionProvider {
        return match ($loginType) {
            SftpStorage::LOGIN_TYPE_CREDENTIALS => $this->getConnectionWithPassword($host, $username, $password, $port),
            SftpStorage::LOGIN_TYPE_PRIVATE_KEY => $this->getConnectionWithPrivateKey($host, $username, $privateKey, $port),
            default => throw new \LogicException(sprintf('Unsupported login type "%s"', $loginType)),
        };
    }

    private function getConnectionWithPassword(
        string $host,
        string $username,
        string $password,
        int $port
    ): SftpConnectionProvider {
        return new SftpConnectionProvider(
            $host,
            $username,
            $password,
            null,
            null,
            $port,
            self::USE_AGENT,
            self::TIMEOUT,
            self::MAX_RETRIES,
        );
    }

    private function getConnectionWithPrivateKey(
        string $host,
        string $username,
        string $privateKey,
        int $port
    ): SftpConnectionProvider {
        return new SftpConnectionProvider(
            $host,
            $username,
            null,
            $privateKey,
            null,
            $port,
            self::USE_AGENT,
            self::TIMEOUT,
            self::MAX_RETRIES,
        );
    }
}
