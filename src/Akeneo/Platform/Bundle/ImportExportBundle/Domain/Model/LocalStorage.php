<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model;

final class LocalStorage implements StorageInterface
{
    public const TYPE = 'local';

    public function __construct(private string $filePath)
    {
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'file_path' => $this->filePath,
        ];
    }
}
