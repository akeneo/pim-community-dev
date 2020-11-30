<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductKeyIndicatorsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetKeyIndicators implements GetKeyIndicatorsInterface
{
    private GetProductKeyIndicatorsQueryInterface $getProductKeyIndicatorsQuery;

    /** @var KeyIndicatorCode[] */
    private array $keyIndicatorCodes;

    public function __construct(GetProductKeyIndicatorsQueryInterface $getProductKeyIndicatorsQuery, string... $keyIndicators)
    {
        $this->getProductKeyIndicatorsQuery = $getProductKeyIndicatorsQuery;
        $this->keyIndicatorCodes = array_map(fn ($keyIndicator) => new KeyIndicatorCode($keyIndicator), $keyIndicators);
    }

    public function all(ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        return $this->formatKeyIndicators($this->getProductKeyIndicatorsQuery->all($channelCode, $localeCode, ...$this->keyIndicatorCodes));
    }

    public function byFamily(ChannelCode $channelCode, LocaleCode $localeCode, FamilyCode $family): array
    {
        return $this->formatKeyIndicators($this->getProductKeyIndicatorsQuery->byFamily($channelCode, $localeCode, $family, ...$this->keyIndicatorCodes));
    }

    public function byCategory(ChannelCode $channelCode, LocaleCode $localeCode, CategoryCode $category): array
    {
        return $this->formatKeyIndicators($this->getProductKeyIndicatorsQuery->byCategory($channelCode, $localeCode, $category, ...$this->keyIndicatorCodes));
    }

    private function formatKeyIndicators(array $keyIndicators): array
    {
        $formattedKeyIndicators = [];
        foreach ($keyIndicators as $keyIndicator) {
            Assert::isInstanceOf($keyIndicator, KeyIndicator::class);
            $formattedKeyIndicators[strval($keyIndicator->getCode())] = [
                'ratioGood' => $keyIndicator->getRatioGood(),
                'totalGood' => $keyIndicator->getTotalGood(),
                'totalToImprove' => $keyIndicator->getTotalToImprove(),
            ];
        }

        return $formattedKeyIndicators;
    }
}
