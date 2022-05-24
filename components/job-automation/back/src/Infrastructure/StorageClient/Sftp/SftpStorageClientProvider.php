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
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\SftpStorage;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;

final class SftpStorageClientProvider implements StorageClientProviderInterface
{
    private const MAX_RETRIES = 4;
    private const USE_AGENT = false;
    private const TIMEOUT = 10;

    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        if (!$storage instanceof SftpStorage) {
            throw new \InvalidArgumentException('The provider only support SftpStorage');
        }

        $dirname = dirname($storage->getFilePath());

        $connection = new SftpConnectionProvider(
            $storage->getHost(),
            $storage->getUsername(),
            $storage->getPassword(),
            null,
            null,
            $storage->getPort(),
            self::USE_AGENT,
            self::TIMEOUT,
            self::MAX_RETRIES,
        );

        return new FileSystemStorageClient(new Filesystem(new SftpAdapter($connection, $dirname)));
    }

    public function supports(StorageInterface $storage): bool
    {
        return $storage instanceof SftpStorage;
    }
}
