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

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\RemoteStorageClientInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\PhpseclibV3\UnableToConnectToSftpHost;

final class SftpStorageClient extends FileSystemStorageClient implements RemoteStorageClientInterface
{
    public function __construct(
        private readonly FilesystemOperator $filesystemOperator,
        private readonly SftpConnectionProvider $sftpConnectionProvider,
    ) {
        parent::__construct($this->filesystemOperator);
    }

    public function isConnectionValid(): bool
    {
        try {
            $this->sftpConnectionProvider->provideConnection();
        } catch (UnableToConnectToSftpHost) {
            return false;
        }

        return true;
    }
}
