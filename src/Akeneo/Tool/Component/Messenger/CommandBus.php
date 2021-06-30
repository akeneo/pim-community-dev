<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Messenger;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommandBus
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @param object|Envelope $command
     *
     * @return mixed The handler returned value
     */
    public function execute($command)
    {
        return $this->handle($command);
    }
}
