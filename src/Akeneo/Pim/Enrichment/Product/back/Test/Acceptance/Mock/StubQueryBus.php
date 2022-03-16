<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Mock;

use Akeneo\Pim\Enrichment\Product\Domain\QueryBus;

/**
 * @todo declare the service to override MessengerQueryBus in acceptance tests
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StubQueryBus implements QueryBus
{
    /** @var array<string, mixed> */
    private array $results = [];

    public function execute(object $query): mixed
    {
        if (!\array_key_exists(get_class($query), $this->results)) {
            throw new \RuntimeException('Stub is not defined');
        }

        return $this->results[get_class($query)];
    }

    public function willReturn(object $query, mixed $result): void
    {
        // @todo: properties of the command are not taken in account.
        $this->results[get_class($query)] = $result;
    }
}
