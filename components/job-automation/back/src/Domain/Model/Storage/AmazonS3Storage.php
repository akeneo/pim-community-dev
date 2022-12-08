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

namespace Akeneo\Platform\JobAutomation\Domain\Model\Storage;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;

final class AmazonS3Storage implements StorageInterface
{
    public const TYPE = 'amazon_s3';

    public function __construct(
        private string $region,
        private string $bucket,
        private string $key,
        private string $secret,
        private string $filePath,
    ) {
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
