<?php

namespace Akeneo\Platform\Job\Infrastructure\Flysystem\Sftp;

use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage\JobInstanceRemoteStorage;
use League\Flysystem\PhpseclibV2\SftpAdapter;

class SftpAdapterFactory
{
    public static function fromJobInstanceRemoteStorage(JobInstanceRemoteStorage $jobInstanceRemoteStorage): SftpAdapter {
        $sftpConnectionProvider = SftpConnectionProviderFactory::fromJobInstanceRemoteStorage($jobInstanceRemoteStorage);

        return new SftpAdapter(
            $sftpConnectionProvider,
            $jobInstanceRemoteStorage->getRoot(),
        );
    }
}
