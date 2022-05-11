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

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;


interface StorageClientInterface
{
    public function fileExists(string $filePath): bool;

    /**
     * @param resource $content
     */
    public function writeStream(string $filePath, $content): void;

    /**
     * @return resource
     */
    public function readStream(string $filePath);
}
