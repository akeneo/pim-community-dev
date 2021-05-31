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

namespace Akeneo\AssetManager\Domain\Model;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

class Image
{
    private ?FileInfoInterface $file = null;

    private function __construct(?FileInfoInterface $file)
    {
        $this->file = $file;
    }

    public static function fromFileInfo(FileInfoInterface $fileInfo): self
    {
        return new self($fileInfo);
    }

    public static function createEmpty(): self
    {
        return new self(null);
    }

    public function getKey(): string
    {
        return $this->file->getKey();
    }

    public function isEmpty(): bool
    {
        return null === $this->file;
    }

    public function normalize(): ?array
    {
        if ($this->isEmpty()) {
            return null;
        }

        return [
            'filePath' => $this->file->getKey(),
            'originalFilename' => $this->file->getOriginalFilename()
        ];
    }
}
