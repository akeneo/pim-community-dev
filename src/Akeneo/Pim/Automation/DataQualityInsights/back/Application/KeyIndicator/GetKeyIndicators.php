<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator;

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
    /** @var KeyIndicatorCode[] */
    private array $keyIndicatorCodes;

    public function __construct(
        private GetProductKeyIndicatorsQueryInterface $getProductKeyIndicatorsQuery,
        private GetProductKeyIndicatorsQueryInterface $getProductModelKeyIndicatorsQuery,
        ProductKeyIndicatorsByFeatureRegistry $productKeyIndicatorsRegistry
    ) {
        $this->keyIndicatorCodes = $productKeyIndicatorsRegistry->getCodes();
    }

    public function all(ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $productKeyIndicators = $this->getProductKeyIndicatorsQuery->all($channelCode, $localeCode, ...$this->keyIndicatorCodes);
        $productModelKeyIndicators = $this->getProductModelKeyIndicatorsQuery->all($channelCode, $localeCode, ...$this->keyIndicatorCodes);
        return $this->formatKeyIndicators($productKeyIndicators, $productModelKeyIndicators);
    }

    public function byFamily(ChannelCode $channelCode, LocaleCode $localeCode, FamilyCode $family): array
    {
        $productKeyIndicators = $this->getProductKeyIndicatorsQuery->byFamily($channelCode, $localeCode, $family, ...$this->keyIndicatorCodes);
        $productModelKeyIndicators = $this->getProductModelKeyIndicatorsQuery->byFamily($channelCode, $localeCode, $family, ...$this->keyIndicatorCodes);
        return $this->formatKeyIndicators($productKeyIndicators, $productModelKeyIndicators);
    }

    public function byCategory(ChannelCode $channelCode, LocaleCode $localeCode, CategoryCode $category): array
    {
        $productKeyIndicators = $this->getProductKeyIndicatorsQuery->byCategory($channelCode, $localeCode, $category, ...$this->keyIndicatorCodes);
        $productModelKeyIndicators = $this->getProductModelKeyIndicatorsQuery->byCategory($channelCode, $localeCode, $category, ...$this->keyIndicatorCodes);
        return $this->formatKeyIndicators($productKeyIndicators, $productModelKeyIndicators);
    }

    private function formatKeyIndicators(array $productKeyIndicators, array $productModelKeyIndicators): array
    {
        $formattedKeyIndicators = [];
        foreach ($this->keyIndicatorCodes as $code) {
            $formattedKeyIndicators[(string)$code] = [
                'products' => ['totalGood' => 0, 'totalToImprove' => 0],
                'product_models' => ['totalGood' => 0, 'totalToImprove' => 0]
            ];
        };

        foreach ($productKeyIndicators as $productKeyIndicator) {
            Assert::isInstanceOf($productKeyIndicator, KeyIndicator::class);
            $formattedKeyIndicators[(string)$productKeyIndicator->getCode()]['products'] = [
                'totalGood' => $productKeyIndicator->getTotalGood(),
                'totalToImprove' => $productKeyIndicator->getTotalToImprove(),
            ];
        }
        foreach ($productModelKeyIndicators as $productModelKeyIndicator) {
            Assert::isInstanceOf($productModelKeyIndicator, KeyIndicator::class);
            $formattedKeyIndicators[(string)$productModelKeyIndicator->getCode()]['product_models'] = [
                'totalGood' => $productModelKeyIndicator->getTotalGood(),
                'totalToImprove' => $productModelKeyIndicator->getTotalToImprove(),
            ];
        }

        return $formattedKeyIndicators;
    }
}
