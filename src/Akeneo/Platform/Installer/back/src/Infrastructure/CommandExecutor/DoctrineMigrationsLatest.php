<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\CommandExecutor;

final class DoctrineMigrationsLatest extends AbstractCommandExecutor implements DoctrineMigrationsLatestInterface
{
    public function getName(): string
    {
        return 'doctrine:migrations:latest';
    }
}
