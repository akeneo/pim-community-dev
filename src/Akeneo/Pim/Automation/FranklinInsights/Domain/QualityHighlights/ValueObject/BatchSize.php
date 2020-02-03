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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject;

final class BatchSize
{
    /** @var int */
    private $batchSize;

    public function __construct(int $batchSize)
    {
        if ($batchSize <= 0) {
            throw new \InvalidArgumentException('A batch size must be an integer greater than zero.');
        }

        $this->batchSize = $batchSize;
    }

    public function toInt(): int
    {
        return $this->batchSize;
    }
}
