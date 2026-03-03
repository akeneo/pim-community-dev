<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage;

final class DownloadFileFromStorageCommand
{
    public function __construct(
        public array $normalizedStorage,
        public string $workingDirectory,
    ) {
    }
}
