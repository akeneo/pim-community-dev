<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\FilesystemPurger;

use Akeneo\Platform\Installer\Domain\Service\FilesystemPurgerInterface;
use League\Flysystem\FilesystemOperator;

class FilesystemPurger implements FilesystemPurgerInterface
{
    public function execute(FilesystemOperator $filesystem): void
    {
        $filesystem->deleteDirectory('./');
    }
}
