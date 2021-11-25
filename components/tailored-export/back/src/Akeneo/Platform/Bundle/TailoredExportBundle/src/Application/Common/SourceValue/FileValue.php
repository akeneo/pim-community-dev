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

namespace Akeneo\Platform\TailoredExport\Application\Common\SourceValue;

class FileValue implements SourceValueInterface
{
    private string $entityIdentifier;
    private string $storage;
    private string $key;
    private string $originalFilename;
    private ?string $channel;
    private ?string $locale;

    public function __construct(
        string $entityIdentifier,
        string $storage,
        string $key,
        string $originalFilename,
        ?string $channel,
        ?string $locale
    ) {
        $this->entityIdentifier = $entityIdentifier;
        $this->storage = $storage;
        $this->key = $key;
        $this->originalFilename = $originalFilename;
        $this->channel = $channel;
        $this->locale = $locale;
    }

    public function getEntityIdentifier(): string
    {
        return $this->entityIdentifier;
    }

    public function getStorage(): string
    {
        return $this->storage;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getChannelReference(): ?string
    {
        return $this->channel;
    }

    public function getLocaleReference(): ?string
    {
        return $this->locale;
    }
}
