<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Domain\Model;

class Image
{
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public static function fromString(string $key): self
    {
      return new self($key);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function normalize(): array
    {
        return [
            'filePath' => $this->key,
            'originalFilename' => basename($this->key)
        ];
    }
}
