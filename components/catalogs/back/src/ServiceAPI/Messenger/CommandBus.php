<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Messenger;

use Akeneo\Catalogs\ServiceAPI\Command\CommandInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function execute(CommandInterface $command): void
    {
        try {
            $this->handle($command);
        } catch (HandlerFailedException $e) {
            if (null === $e->getPrevious()) {
                throw $e;
            }

            throw $e->getPrevious();
        }
    }
}
