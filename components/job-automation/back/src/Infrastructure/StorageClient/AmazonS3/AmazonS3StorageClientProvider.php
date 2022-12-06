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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\AmazonS3Storage;
use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

final class AmazonS3StorageClientProvider implements StorageClientProviderInterface
{
    public function __construct(
        private readonly Encrypter $encrypter,
    ) {
    }

    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        if (!$storage instanceof AmazonS3Storage) {
            throw new \InvalidArgumentException('The provider only support AmazonS3Storage');
        }

        $encryptionKey = $this->getEncryptionKey($storage);

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $storage->getRegion(),
            'credentials' => [
                'key' => $storage->getKey(),
                'secret' => $this->encrypter->decrypt($storage->getSecret(), $encryptionKey),
            ],
            'use_path_style_endpoint' => true,
        ]);

        $adapter = new AwsS3V3Adapter(
            $s3Client,
            $storage->getBucket(),
        );

        return new AmazonS3StorageClient(
            new Filesystem($adapter),
            $s3Client,
            $storage->getBucket(),
        );
    }

    public function supports(StorageInterface $storage): bool
    {
        return $storage instanceof AmazonS3Storage;
    }

    private function getEncryptionKey(AmazonS3Storage $storage): string
    {
        return sprintf(
            '%s:%s:%s',
            $storage->getRegion(),
            $storage->getBucket(),
            $storage->getKey(),
        );
    }
}
