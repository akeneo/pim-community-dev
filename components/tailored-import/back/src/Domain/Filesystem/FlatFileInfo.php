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

namespace Akeneo\Platform\TailoredImport\Domain\Filesystem;

use Webmozart\Assert\Assert;

class FlatFileInfo
{
    public function __construct(
        private string $fileKey,
    ) {
        Assert::stringNotEmpty($fileKey);
    }

    public function normalize(): array
    {
        return [
            'file_key' => $this->fileKey,
        ];
    }
}
