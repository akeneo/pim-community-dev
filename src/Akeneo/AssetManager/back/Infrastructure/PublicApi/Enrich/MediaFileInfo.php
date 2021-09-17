<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Enrich;

class MediaFileInfo
{
    private string $fileKey;
    private string $originalFilename;
    private string $storage;
    private ?string $channel;
    private ?string $locale;

    public function __construct(string $fileKey, string $originalFilename, string $storage, ?string $channel, ?string $locale)
    {
        $this->fileKey = $fileKey;
        $this->originalFilename = $originalFilename;
        $this->storage = $storage;
        $this->channel = $channel;
        $this->locale = $locale;
    }

    public function getFileKey(): string
    {
        return $this->fileKey;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getStorage(): string
    {
        return $this->storage;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
