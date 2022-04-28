<?php

namespace Akeneo\Platform\Job\Infrastructure\Flysystem\Sftp;

use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage\JobInstanceRemoteStorage;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;

class SftpConnectionProviderFactory
{
    public static function fromJobInstanceRemoteStorage(JobInstanceRemoteStorage $jobInstanceRemoteStorage): SftpConnectionProvider
    {
        $normalizedJobInstanceRemoteStorage = $jobInstanceRemoteStorage->normalize();

        return match ($normalizedJobInstanceRemoteStorage['login']['type']) {
            JobInstanceRemoteStorage::PASSWORD_LOGIN_TYPE => new SftpConnectionProvider(
                $normalizedJobInstanceRemoteStorage['host'],
                $normalizedJobInstanceRemoteStorage['username'],
                $normalizedJobInstanceRemoteStorage['login']['password'],
                null,
                null,
                $normalizedJobInstanceRemoteStorage['port'],
            ),
            JobInstanceRemoteStorage::PRIVATE_KEY_LOGIN_TYPE => new SftpConnectionProvider(
                $normalizedJobInstanceRemoteStorage['host'],
                $normalizedJobInstanceRemoteStorage['username'],
                null,
                $normalizedJobInstanceRemoteStorage['login']['private_key'],
                $normalizedJobInstanceRemoteStorage['login']['passphrase'],
                $normalizedJobInstanceRemoteStorage['port'],
            ),
            default => throw new \InvalidArgumentException(sprintf('Unknown login type "%s"', $normalizedJobInstanceRemoteStorage['type'])),
        };
    }
}
