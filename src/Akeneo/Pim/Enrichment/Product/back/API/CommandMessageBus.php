<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

/**
 * This bus is a simpler implementation, it will be replaced by a more generic solution.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CommandMessageBus implements MessageBusInterface
{
    /** @var array<string, callable> */
    private array $handlers;

    /**
     * @param iterable<string, callable> $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers instanceof \Traversable ? iterator_to_array($handlers) : $handlers;
        Assert::allString(\array_keys($this->handlers));
        Assert::allObject($this->handlers);
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $handler = $this->handlers[get_class($message)] ?? null;
        if (null === $handler) {
            throw new UnknownCommandException(\sprintf('No configured handler for the "%s" command', get_class($message)));
        }

        $handler($message);

        return Envelope::wrap($message, $stamps);
    }
}
