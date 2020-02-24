<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ReadProducts
{
    /** @var string */
    private $connectionCode;

    /** @var int[] */
    private $productIds;

    /** @var \DateTimeInterface */
    private $eventDatetime;

    public function __construct(string $connectionCode, array $productIds, \DateTimeInterface $eventDatetime)
    {
        $this->connectionCode = $connectionCode;
        $this->productIds = $productIds;
        $this->eventDatetime = $eventDatetime;
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function productIds(): array
    {
        return $this->productIds;
    }

    public function eventDatetime(): \DateTimeInterface
    {
        return $this->eventDatetime;
    }
}
