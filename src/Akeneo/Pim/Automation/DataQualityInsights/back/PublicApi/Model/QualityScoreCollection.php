<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QualityScoreCollection
{
    public function __construct(public array $qualityScores = [])
    {
    }

    public function getQualityScoreByChannelAndLocale(string $channel, string $locale): ?QualityScore
    {
        return $this->qualityScores[$channel][$locale] ?? null;
    }
}
