<?php

namespace Akeneo\Platform\Installer\Infrastructure\Event;

use Akeneo\Tool\Component\Console\CommandExecutor;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerEvent extends GenericEvent
{
    protected ?CommandExecutor $commandExecutor;

    /**
     * @param CommandExecutor|null $commandExecutor
     * @param string|null $subject
     * @param array<mixed> $arguments
     */
    public function __construct(
        ?CommandExecutor $commandExecutor = null,
        ?string $subject = null,
        array $arguments = []
    ) {
        $this->commandExecutor = $commandExecutor;

        parent::__construct($subject, $arguments);
    }

    public function getCommandExecutor(): ?CommandExecutor
    {
        return $this->commandExecutor;
    }
}
