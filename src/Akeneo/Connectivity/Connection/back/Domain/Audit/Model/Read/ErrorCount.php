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
    private string $connectionCode;

    private int $count;

    public function __construct(string $connectionCode, int $count)
    {
        $this->connectionCode = $connectionCode;
        $this->count = $count;
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
