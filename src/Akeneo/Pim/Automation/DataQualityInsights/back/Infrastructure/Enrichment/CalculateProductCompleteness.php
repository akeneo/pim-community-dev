<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CalculateProductCompleteness implements CalculateProductCompletenessInterface
{
    public function __construct(
        private CompletenessCalculator $completenessCalculator
    ) {
    }

    public function calculate(ProductEntityIdInterface $productUuid): CompletenessCalculationResult
    {
        if (!$productUuid instanceof ProductUuid) {
            throw new \InvalidArgumentException(sprintf('Invalid product uuid: %s', (string) $productUuid));
        }

        $result = new CompletenessCalculationResult();
        $completenessCollection = $this->completenessCalculator->fromProductUuid(Uuid::fromString((string) $productUuid));

        foreach ($completenessCollection as $completeness) {
            $channelCode = new ChannelCode($completeness->channelCode());
            $localeCode = new LocaleCode($completeness->localeCode());
            $result->addRate($channelCode, $localeCode, new Rate($completeness->ratio()));
            $result->addMissingAttributes($channelCode, $localeCode, $completeness->missingAttributeCodes());
            $result->addTotalNumberOfAttributes($channelCode, $localeCode, $completeness->requiredCount());
        }

        return $result;
    }
}
