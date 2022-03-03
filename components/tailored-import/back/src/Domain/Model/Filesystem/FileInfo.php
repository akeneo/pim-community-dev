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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Filesystem;

use Webmozart\Assert\Assert;

class FileInfo
{
    public function __construct(
        private string $fileKey,
        private string $originalFilename,
    ) {
        Assert::stringNotEmpty($fileKey);
        Assert::stringNotEmpty($originalFilename);
    }

    public function normalize(): array
    {
        return [
            'filePath' => $this->fileKey,
            'originalFilename' => $this->originalFilename,
        ];
    }
}
