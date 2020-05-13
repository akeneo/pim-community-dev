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
    /** @var string */
    private $connectionCode;

    /**
     * @var int
     */
    private $count;

    public function __construct(string $connectionCode, int $count)
    {
        $this->connectionCode = $connectionCode;
        $this->count = $count;
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function count(): int
    {
        return $this->count;
    }
}
