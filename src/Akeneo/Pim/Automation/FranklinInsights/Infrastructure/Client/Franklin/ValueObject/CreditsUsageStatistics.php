<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

final class CreditsUsageStatistics
{
    /** @var int */
    private $consumed;

    /** @var int */
    private $left;

    /** @var int */
    private $total;

    public function __construct(array $statistics)
    {
        foreach (['consumed', 'left', 'total'] as $name) {
            if (!isset($statistics[$name])) {
                throw new \InvalidArgumentException(sprintf('Missing credits usage statistic "%s"', $name));
            }
        }

        $this->consumed = $statistics['consumed'];
        $this->left = $statistics['left'];
        $this->total = $statistics['total'];
    }

    public function getConsumed(): int
    {
        return $this->consumed;
    }

    public function getLeft(): int
    {
        return $this->left;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
