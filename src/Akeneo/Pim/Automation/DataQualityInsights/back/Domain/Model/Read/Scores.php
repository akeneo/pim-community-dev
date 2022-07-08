<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Scores
{
    public function __construct(
        private ChannelLocaleRateCollection $scoresAllCriteria,
        private ChannelLocaleRateCollection $scoresPartialCriteria,
    ) {
    }

    public function allCriteria(): ChannelLocaleRateCollection
    {
        return $this->scoresAllCriteria;
    }

    public function partialCriteria(): ChannelLocaleRateCollection
    {
        return $this->scoresPartialCriteria;
    }
}
