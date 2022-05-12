<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Messenger;

use Akeneo\Catalogs\ServiceAPI\Query\Query;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QueryBus
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    /**
     * @template R
     * @param \Akeneo\Catalogs\ServiceAPI\Query\Query<R> $query
     * @return R
     *
     * @psalm-suppress MixedReturnStatement
     * @psalm-suppress MixedInferredReturnType
     */
    public function execute(Query $query): mixed
    {
        try {
            return $this->handle($query);
        } catch (HandlerFailedException $e) {
            if (null === $e->getPrevious()) {
                throw $e;
            }

            throw $e->getPrevious();
        }
    }
}
