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

    private array $extraData;

    public function __construct(KeyIndicatorCode $code, int $totalGood, int $totalToImprove, array $extraData = [])
    {
        $this->code = $code;
        $this->totalGood = $totalGood;
        $this->totalToImprove = $totalToImprove;
        $this->extraData = $extraData;
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
        $ratio = $total === 0 ? 0 : intval(round($this->totalGood / $total * 100));

        return $this->roundRatioExtremities($ratio);
    }

    public function isEmpty(): bool
    {
        return $this->totalToImprove === 0 && $this->totalGood === 0;
    }

    public function getExtraData(): array
    {
        return $this->extraData;
    }

    public function toArray(): array
    {
        return [
            'ratioGood' => $this->getRatioGood(),
            'totalGood' => $this->totalGood,
            'totalToImprove' => $this->totalToImprove,
            'extraData' => $this->extraData,
        ];
    }

    /**
     * Round ratio extremities to avoid having 100% while there's at least one item to improve
     * And to avoid having 0% while there's at least one good item
     */
    private function roundRatioExtremities(int $ratio): int
    {
        if (100 === $ratio && $this->totalToImprove > 0) {
            return 99;
        } elseif (0 === $ratio && $this->totalGood > 0) {
            return 1;
        }

        return $ratio;
    }
}
