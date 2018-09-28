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

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

class Image
{
    /** @var string|null */
    private $key;

    /** @var string|null */
    private $originalFilename;

    private function __construct(?string $key, ?string $originalFilename)
    {
        $this->key = $key;
        $this->originalFilename = $originalFilename;
    }

    public static function fromFileInfo(FileInfoInterface $fileInfo): self
    {
        return new self($fileInfo->getKey(), $fileInfo->getOriginalFilename());
    }

    public static function createEmpty(): self
    {
        return new self(null, null);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function isEmpty(): bool
    {
        return null === $this->key;
    }

    public function normalize(): ?array
    {
        if ($this->isEmpty()) {
            return null;
        }

        return [
            'filePath' => $this->key,
            'originalFilename' => $this->originalFilename
        ];
    }
}
