<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\CommandExecutor;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

interface CommandExecutorInterface
{
    public function getName(): string;

    /**
     * @param mixed[]|null $options
     */
    public function execute(?array $options, bool $withOutput = false): null|OutputInterface;

    public function getApplication(): Application;
}
