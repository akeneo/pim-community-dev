<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ErrorCountPerConnection
{
    /** @var ErrorCount[] */
    private array $errorCounts = [];

    /**
     * @param ErrorCount[] $errorCounts
     */
    public function __construct(array $errorCounts)
    {
        foreach ($errorCounts as $errorCount) {
            if (!$errorCount instanceof ErrorCount) {
                throw new \InvalidArgumentException('One of the given element is not an ErrorCount object.');
            }

            $this->errorCounts[] = $errorCount;
        }
    }

    /**
     * @return array<string, int>
     */
    public function normalize(): array
    {
        $errorCountPerConnection = [];

        foreach ($this->errorCounts as $errorCount) {
            $data = $errorCount->normalize();
            $errorCountPerConnection[$data['connection_code']] = (int) $data['count'];
        }

        return $errorCountPerConnection;
    }
}
