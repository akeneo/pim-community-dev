<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Registry;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProcessMessageHandlerRegistry
{
    /** @var object[] */
    private array $handlers = [];

    public function registerHandler(object $handler, string $consumerName): void
    {
        if (\array_key_exists($consumerName, $this->handlers)) {
            throw new \LogicException(sprintf('An handler is already registered for the "%s" consumer', $consumerName));
        }

        Assert::isCallable($handler, 'The handler must be callable');

        $this->handlers[$consumerName] = $handler;
    }

    /**
     * @throw \LogicException
     */
    public function getHandler(string $consumerName): object
    {
        if (!\array_key_exists($consumerName, $this->handlers)) {
            throw new \LogicException(sprintf('No handler found for the "%s" consumer', $consumerName));
        }

        return $this->handlers[$consumerName];
    }
}
