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
    /** @var string */
    private $key;

    /** @var string */
    private $originalFilename;

    public function __construct(string $key, string $originalFilename)
    {
        $this->key = $key;
        $this->originalFilename = $originalFilename;
    }

    public static function fromFileInfo(string $key, string $originalFilename): self
    {
        return new self($key, $originalFilename);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function normalize(): array
    {
        return [
            'filePath' => $this->key,
            'originalFilename' => $this->originalFilename
        ];
    }
}
