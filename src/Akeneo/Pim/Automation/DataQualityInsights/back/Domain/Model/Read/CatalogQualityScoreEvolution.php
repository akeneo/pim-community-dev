<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistribution;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;

final class CatalogQualityScoreEvolution
{
    private const NUMBER_OF_PAST_MONTHS_TO_RETURN = 5;

    private \DateTimeImmutable $referenceDate;

    private array $scores;

    private string $channel;

    private string $locale;

    public function __construct(\DateTimeImmutable $referenceDate, array $scores, string $channel, string $locale)
    {
        $this->referenceDate = $referenceDate;
        $this->scores = $scores;
        $this->channel = $channel;
        $this->locale = $locale;
    }

    public function toArray(): array
    {
        $data = $this->initLastMonthsWithEmptyData();

        foreach (array_keys($data) as $period) {
            if (isset($this->scores['monthly'][$period][$this->channel][$this->locale])) {
                $ranksDistribution = new RanksDistribution($this->scores['monthly'][$period][$this->channel][$this->locale]);
                $data[$period] = $ranksDistribution->getAverageRank()->toLetter();
            }
        }

        $currentCatalogAverageRank = isset($this->scores['average_ranks'][$this->channel][$this->locale]) ? (Rank::fromString($this->scores['average_ranks'][$this->channel][$this->locale]))->toLetter() : null;

        $data = $this->addCurrentCatalogAverageRankToCurrentMonth($data, $currentCatalogAverageRank);

        $productScoreEvolution['average_rank'] = $currentCatalogAverageRank;
        $productScoreEvolution['data'] = $data;

        return $productScoreEvolution;
    }

    private function initLastMonthsWithEmptyData(): array
    {
        $data = [];
        for ($i = self::NUMBER_OF_PAST_MONTHS_TO_RETURN; $i >= 0; $i--) {
            $newDate = $this->referenceDate->modify('last day of ' . $i . ' month ago');
            $data[$newDate->format('Y-m-d')] = null;
        }

        return $data;
    }

    private function addCurrentCatalogAverageRankToCurrentMonth(array $data, ?string $currentAverageRank): array
    {
        $data[array_key_last($data)] = $currentAverageRank;

        return $data;
    }
}
