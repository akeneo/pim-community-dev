<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
