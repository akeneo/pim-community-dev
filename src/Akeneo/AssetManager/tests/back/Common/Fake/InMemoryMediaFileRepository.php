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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Repository\MediaFileNotFoundException;
use Akeneo\AssetManager\Domain\Repository\MediaFileRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryMediaFileRepository implements MediaFileRepositoryInterface
{
    /** @var FileInfo[] */
    private array $mediaFiles = [];

    public function getByIdentifier(string $identifier): FileInfo
    {
        if (!array_key_exists($identifier, $this->mediaFiles)) {
            throw MediaFileNotFoundException::withIdentifier($identifier);
        }

        return $this->mediaFiles[$identifier];
    }

    public function save(FileInfo $mediaFile): void
    {
        $this->mediaFiles[$mediaFile->getKey()] = $mediaFile;
    }
}
