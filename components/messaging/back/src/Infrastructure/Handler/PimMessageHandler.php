<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure\Handler;

use Akeneo\Pim\Platform\Messaging\Domain\PimMessageHandlerInterface;
use Akeneo\Pim\Platform\Messaging\Domain\PimMessageInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PimMessageHandler implements MessageHandlerInterface
{
    /** @var array<string, PimMessageHandler> */
    private array $handlers;

    public function registerHandler(string $messageClass, PimMessageHandlerInterface $handler)
    {
        $this->handlers[$messageClass] = $handler;
    }

    public function __invoke(PimMessageInterface $message)
    {
        $messageClass = get_class($message);
        print_r(get_class($this) . ': received a ' . $messageClass . "\n");
        $handler = $this->handlers[$messageClass] ?? null;
        Assert::notNull($handler, \sprintf('No handler for message %s', $messageClass));

        $handler($message);
    }
}
