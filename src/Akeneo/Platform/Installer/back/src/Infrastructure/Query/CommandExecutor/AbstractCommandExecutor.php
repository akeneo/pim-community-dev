<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query\CommandExecutor;

use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\CommandExecutorInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractCommandExecutor implements CommandExecutorInterface
{
    public const COMMAND_NAME = 'implement-command-name';
    public function __construct(
        private readonly KernelInterface $kernel
    ) {}

    public function execute(?array $options): void
    {
        $command = [
            'command' => self::COMMAND_NAME
        ];

        if ($options) {
            $command = \array_merge($command, $options);
        }

        $this->getApplication()->run(new ArrayInput($command), new NullOutput());
    }

    public function getApplication(): Application
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
    }
}
