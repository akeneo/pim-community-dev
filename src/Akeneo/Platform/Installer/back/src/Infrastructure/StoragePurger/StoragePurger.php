<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\StoragePurger;

use Akeneo\Platform\Installer\Domain\Service\StoragePurgerInterface;
use League\Flysystem\FilesystemOperator;

class StoragePurger implements StoragePurgerInterface
{
    public function __construct(
        private readonly FilesystemOperator $catalogStorage,
    ) {
    }

    public function execute(): void
    {
        $this->purgeStorage($this->catalogStorage);
    }

    public function purgeStorage(FilesystemOperator $storage): void
    {
        foreach ($storage->listContents('.') as $content) {
            if ($content->isDir()) {
                $storage->deleteDirectory($content->path());
            } else {
                $storage->delete($content->path());
            }
        }
    }
}
