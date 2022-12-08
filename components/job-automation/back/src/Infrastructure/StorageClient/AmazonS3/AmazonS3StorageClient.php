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

namespace Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\AmazonS3;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\RemoteStorageClientInterface;
use Aws\S3\S3ClientInterface;
use League\Flysystem\FilesystemOperator;

final class AmazonS3StorageClient extends FileSystemStorageClient implements RemoteStorageClientInterface
{
    public function __construct(
        private readonly FilesystemOperator $filesystemOperator,
        private readonly S3ClientInterface $s3Client,
        private readonly string $bucketName,
    ) {
        parent::__construct($this->filesystemOperator);
    }

    public function isConnectionValid(): bool
    {
        return $this->s3Client->doesBucketExist($this->bucketName);
    }
}
