<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Bus;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class CommandBus
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    /**
     * @return mixed The handler returned value
     */
    public function dispatch(object $command): mixed
    {
        try {
            return $this->handle($command);
        } catch (HandlerFailedException $e) {
            if (!$e->getPrevious() instanceof \Throwable) {
                throw $e;
            }

            throw $e->getPrevious();
        }
    }
}
