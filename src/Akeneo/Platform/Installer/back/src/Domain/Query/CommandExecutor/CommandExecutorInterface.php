<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Domain\Query\CommandExecutor;

use Symfony\Bundle\FrameworkBundle\Console\Application;

interface CommandExecutorInterface
{
    public function execute(?array $options): void;

    public function getApplication(): Application;
}
