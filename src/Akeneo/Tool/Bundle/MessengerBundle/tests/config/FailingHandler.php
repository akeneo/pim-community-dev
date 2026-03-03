<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\config;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FailingHandler
{
    public function __construct(private readonly HandlerObserver $handlerObserver)
    {
    }

    public function __invoke(Message2 $message): void
    {
        $this->handlerObserver->handlerWasExecuted(self::class, $message);

        throw new \Exception('This is a failing handler, what did you expect?');
    }
}
