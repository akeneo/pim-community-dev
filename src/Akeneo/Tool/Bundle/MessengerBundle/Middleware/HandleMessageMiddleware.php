<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\ConsumerNameStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy\MessageWrapper;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Webmozart\Assert\Assert;

final class HandleMessageMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly MessageHandlerInterface $messageHandler,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        Assert::isInstanceOf($envelope->getMessage(), MessageWrapper::class, 'The message must be a MessageWrapper');

        $consumerNameStamp = $envelope->last(ConsumerNameStamp::class);
        if (null === $consumerNameStamp) {
            throw new \LogicException('The envelope must be stamped with its consumer name');
        }

        try {
            ($this->messageHandler)($envelope->getMessage(), (string) $consumerNameStamp);
        } catch (\Throwable $e) {
            throw new HandlerFailedException($envelope, [$e]);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
