<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Event\Connector;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ReadProductsEvent
{
    const EVENT_API_TYPE = 'event_api';
    const REST_API_TYPE = 'rest_api';

    private int $count;

    private ?string $connectionCode;

    private string $origin;

    public function __construct(int $count, $origin = self::REST_API_TYPE, ?string $connectionCode = null)
    {
        $this->count = $count;
        $this->connectionCode = $connectionCode;
        $this->origin = $origin;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getConnectionCode(): ?string
    {
        return $this->connectionCode;
    }

    public function isEventApi(): bool
    {
        return $this->origin === self::EVENT_API_TYPE;
    }
}
