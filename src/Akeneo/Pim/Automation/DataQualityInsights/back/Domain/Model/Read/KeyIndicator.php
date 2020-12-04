<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class KeyIndicator
{
    private KeyIndicatorCode $code;

    private int $totalGood;

    private int $totalToImprove;

    public function __construct(KeyIndicatorCode $code, int $totalGood, int $totalToImprove)
    {
        $this->code = $code;
        $this->totalGood = $totalGood;
        $this->totalToImprove = $totalToImprove;
    }

    public function getCode(): KeyIndicatorCode
    {
        return $this->code;
    }

    public function getTotalGood(): int
    {
        return $this->totalGood;
    }

    public function getTotalToImprove(): int
    {
        return $this->totalToImprove;
    }

    public function getRatioGood(): int
    {
        $total = $this->totalGood + $this->totalToImprove;

        return $total === 0 ? 0 : intval(round($this->totalGood / $total * 100));
    }

    public function isEmpty(): bool
    {
        return $this->totalToImprove === 0 && $this->totalGood === 0;
    }

    public function toArray(): array
    {
        return [
            'ratioGood' => $this->getRatioGood(),
            'totalGood' => $this->totalGood,
            'totalToImprove' => $this->totalToImprove,
        ];
    }
}
