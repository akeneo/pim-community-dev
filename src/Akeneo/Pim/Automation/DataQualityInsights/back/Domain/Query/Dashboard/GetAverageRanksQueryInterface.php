<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAverageRanksQueryInterface
{
    public function byFamilies(ChannelCode $channelCode, LocaleCode $localeCode, array $familyCodes): array;

    public function byCategories(ChannelCode $channelCode, LocaleCode $localeCode, array $categoryCodes): array;
}
