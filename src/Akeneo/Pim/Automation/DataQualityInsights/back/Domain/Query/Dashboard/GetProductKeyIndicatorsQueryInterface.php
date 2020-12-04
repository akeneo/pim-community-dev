<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Get key indicators related to products
 */
interface GetProductKeyIndicatorsQueryInterface
{
    /**
     * @return KeyIndicator[]
     */
    public function all(ChannelCode $channelCode, LocaleCode $localeCode, KeyIndicatorCode... $keyIndicatorCodes): array;

    /**
     * @return KeyIndicator[]
     */
    public function byFamily(ChannelCode $channelCode, LocaleCode $localeCode, FamilyCode $family, KeyIndicatorCode... $keyIndicatorCodes): array;

    /**
     * @return KeyIndicator[]
     */
    public function byCategory(ChannelCode $channelCode, LocaleCode $localeCode, CategoryCode $category, KeyIndicatorCode... $keyIndicatorCodes): array;
}
