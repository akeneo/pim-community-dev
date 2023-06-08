<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Registry;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProcessMessageHandlerRegistrySpec extends ObjectBehavior
{
    public function it_returns_the_handler() {
        $handler1 = new class {
            public function __invoke(object $message) {}
        };
        $handler2 = new class {
            public function __invoke(object $message) {}
        };

        $this->registerHandler($handler1, 'consumer1');
        $this->registerHandler($handler2, 'consumer2');

        $this->getHandler('consumer1')->shouldReturn($handler1);
        $this->getHandler('consumer2')->shouldReturn($handler2);
    }

    public function it_throws_an_exception_when_no_handler_is_found()
    {
        $this->shouldThrow(\LogicException::class)->during('getHandler', ['unknown']);
    }

    public function it_throws_an_exception_when_handler_is_registerer_twice_for_a_consumer()
    {
        $handler1 = new class {
            public function __invoke(object $message) {}
        };
        $handler2 = new class {
            public function __invoke(object $message) {}
        };

        $this->registerHandler($handler1, 'consumer1');

        $this->shouldThrow(\LogicException::class)->during('registerHandler', [$handler2, 'consumer1']);
    }

    public function it_throws_an_exception_when_handler_is_not_invokable(): void
    {
        $handler = new class {};

        $this->shouldThrow(\InvalidArgumentException::class)->during('registerHandler', [$handler, 'not_invokable']);
    }
}
