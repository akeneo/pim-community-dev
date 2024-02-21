<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model;

final class ManualUploadStorage implements StorageInterface
{
    public const TYPE = 'manual_upload';

    public function __construct(private string $filePath)
    {
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
