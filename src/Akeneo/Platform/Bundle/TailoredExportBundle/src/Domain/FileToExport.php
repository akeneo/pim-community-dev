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

namespace Akeneo\Platform\TailoredExport\Domain;

final class FileToExport
{
    private string $key;
    private string $storage;
    private string $path;

    public function __construct(string $key, string $storage, string $path)
    {
        $this->key = $key;
        $this->storage = $storage;
        $this->path = $path;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getStorage(): string
    {
        return $this->storage;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
