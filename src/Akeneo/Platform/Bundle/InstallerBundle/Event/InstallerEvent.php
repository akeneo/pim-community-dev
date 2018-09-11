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
    /** @var CommandExecutor */
    protected $commandExecutor;

    /**
     * @param CommandExecutor $commandExecutor
     * @param null|string $subject
     * @param array $arguments
     */
    public function __construct(CommandExecutor $commandExecutor, ?string $subject = null, array $arguments = [])
    {
        $this->commandExecutor = $commandExecutor;

        parent::__construct($subject, $arguments);
    }

    /**
     * @return CommandExecutor
     */
    public function getCommandExecutor(): CommandExecutor
    {
        return $this->commandExecutor;
    }
}
