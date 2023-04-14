<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query\CommandExecutor;

use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\DoctrineMigrationsVersionInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

final class DoctrineMigrationsVersion extends AbstractCommandExecutor implements DoctrineMigrationsVersionInterface
{
    public function getName(): string
    {
        return 'doctrine:migrations:version';
    }
}
