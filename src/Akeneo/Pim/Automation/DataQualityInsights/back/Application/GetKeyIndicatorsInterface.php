<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetKeyIndicatorsInterface
{
    public function all(ChannelCode $channelCode, LocaleCode $localeCode): array;

    public function byFamily(ChannelCode $channelCode, LocaleCode $localeCode, FamilyCode $family): array;

    public function byCategory(ChannelCode $channelCode, LocaleCode $localeCode, CategoryCode $category): array;
}
