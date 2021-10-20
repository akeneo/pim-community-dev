<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

class ProductScore
{
    public string $letter;
    public int $rate;

    public function __construct(string $letter, int $rate)
    {
        $this->letter = $letter;
        $this->rate = $rate;
    }

    public function getLetter(): string
    {
        return $this->letter;
    }

    public function getRate(): int
    {
        return $this->rate;
    }
}
