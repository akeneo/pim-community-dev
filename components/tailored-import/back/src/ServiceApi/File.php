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

namespace Akeneo\Platform\TailoredImport\ServiceApi;

use Webmozart\Assert\Assert;

class File
{
    /**
     * @param resource $resource
     */
    public function __construct(
        private string $fileName,
        private $resource,
    ) {
        Assert::resource($resource, 'stream');
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
