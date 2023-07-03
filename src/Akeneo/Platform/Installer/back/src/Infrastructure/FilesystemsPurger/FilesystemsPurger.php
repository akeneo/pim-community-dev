<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\FilesystemsPurger;

use Akeneo\Platform\Installer\Domain\Service\FilesystemPurgerInterface;
use League\Flysystem\FilesystemOperator;

class FilesystemsPurger implements FilesystemPurgerInterface
{
    /**
     * @param iterable<FilesystemOperator> $filesystems
     */
    public function __construct(
        private readonly iterable $filesystems,
    ) {
    }

    public function execute(): void
    {
        foreach ($this->filesystems as $filesystem) {
            $this->purgeFilesystem($filesystem);
        }
    }

    private function purgeFilesystem(FilesystemOperator $filesystem): void
    {
        $filesystem->deleteDirectory('./');
    }
}
