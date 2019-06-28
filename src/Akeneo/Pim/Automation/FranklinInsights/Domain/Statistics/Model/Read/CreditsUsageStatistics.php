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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Statistics\Model\Read;

final class CreditsUsageStatistics
{
    /** @var int */
    private $consumed;

    /** @var int */
    private $left;

    /** @var int */
    private $total;

    public function __construct(int $consumed, int $left, int $total)
    {
        $this->consumed = $consumed;
        $this->left = $left;
        $this->total = $total;
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
