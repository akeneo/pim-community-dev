<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\Event;

use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerEvent extends GenericEvent
{
    protected ?CommandExecutor $commandExecutor;

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
