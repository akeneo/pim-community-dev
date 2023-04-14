<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\DatabaseInstall;

use Symfony\Component\Console\Style\SymfonyStyle;

final class DatabaseInstallCommand
{
    public function __construct(
        private readonly SymfonyStyle $io,
        private readonly array $options,
    )
    {}

    public function getIo(): SymfonyStyle
    {
        return $this->io;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $key): string
    {
        return $this->getOptions()[$key];
    }
}
