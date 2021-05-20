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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\MediaFile;

use Akeneo\AssetManager\Domain\Repository\MediaFileNotFoundException;
use Akeneo\AssetManager\Domain\Repository\MediaFileRepositoryInterface;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlMediaFileRepository implements MediaFileRepositoryInterface
{
    private ApiResourceRepositoryInterface $fileRepository;

    public function __construct(ApiResourceRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function getByIdentifier(string $identifier): FileInfo
    {
        $mediaFile = $this->fileRepository->findOneByIdentifier($identifier);

        if (null === $mediaFile) {
            throw MediaFileNotFoundException::withIdentifier($identifier);
        }

        return $mediaFile;
    }
}
