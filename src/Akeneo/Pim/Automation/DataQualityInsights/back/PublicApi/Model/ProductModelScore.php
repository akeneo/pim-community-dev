<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelScore
{
    private string $letter;
    private int $rate;

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
