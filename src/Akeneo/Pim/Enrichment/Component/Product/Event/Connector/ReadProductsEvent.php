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
    private int $count;

    private ?string $connectionCode;

    public function __construct(int $count, ?string $connectionCode = null)
    {
        $this->count = $count;
        $this->connectionCode = $connectionCode;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getConnectionCode(): ?string
    {
        return $this->connectionCode;
    }
}
