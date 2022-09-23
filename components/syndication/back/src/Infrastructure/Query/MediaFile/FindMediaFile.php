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

namespace Akeneo\Platform\Syndication\Infrastructure\Query\MediaFile;

use Akeneo\Platform\Syndication\Domain\Query\MediaFile\FindMediaFileInterface;
use Akeneo\Platform\Syndication\Domain\Query\MediaFile\MediaFileNotFoundException;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\DBAL\Connection;

class FindMediaFile implements FindMediaFileInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function getByIdentifier(string $fileCode): FileInfoInterface
    {
        $sql = <<<SQL
            SELECT
                id,
                file_key,
                original_filename,
                mime_type,
                storage
            FROM akeneo_file_storage_file_info
            WHERE file_key = :file_code
        SQL;

        $result = $this->sqlConnection->executeQuery($sql, ['file_code' => $fileCode])->fetchAllAssociative();

        if (empty($result)) {
            throw MediaFileNotFoundException::withIdentifier($fileCode);
        }

        $mediaFile = $result[0];

        $fileInfo = new FileInfo();
        $fileInfo->setOriginalFilename($mediaFile['original_filename']);
        $fileInfo->setKey($mediaFile['file_key']);
        $fileInfo->setMimeType($mediaFile['mime_type']);
        $fileInfo->setStorage($mediaFile['storage']);

        return $fileInfo;
    }
}
