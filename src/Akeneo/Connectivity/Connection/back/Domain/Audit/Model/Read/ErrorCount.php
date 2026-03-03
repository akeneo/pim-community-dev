<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ErrorCount
{
    public function __construct(private string $connectionCode, private int $count)
    {
    }

    /**
     * @return array{connection_code: string, count: int}
     */
    public function normalize(): array
    {
        return [
            'connection_code' => $this->connectionCode,
            'count' => $this->count,
        ];
    }
}
