<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Domain\Service;

use League\Flysystem\FilesystemOperator;

interface FilesystemPurgerInterface
{
    public function execute(FilesystemOperator $filesystem): void;
}
