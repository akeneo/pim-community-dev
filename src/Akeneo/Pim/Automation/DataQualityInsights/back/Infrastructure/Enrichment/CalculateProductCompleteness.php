<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdentifierFromProductUuidQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CalculateProductCompleteness implements CalculateProductCompletenessInterface
{
    private GetProductIdentifierFromProductUuidQueryInterface $getProductIdentifierFromProductIdQuery;

    private CompletenessCalculator $completenessCalculator;

    public function __construct(
        GetProductIdentifierFromProductUuidQueryInterface $getProductIdentifierFromProductIdQuery,
        CompletenessCalculator                            $completenessCalculator
    ) {
        $this->completenessCalculator = $completenessCalculator;
        $this->getProductIdentifierFromProductIdQuery = $getProductIdentifierFromProductIdQuery;
    }

    public function calculate(ProductEntityIdInterface $productId): CompletenessCalculationResult
    {
        if (!$productId instanceof ProductUuid) {
            throw new \InvalidArgumentException(sprintf('Invalid product id: %s', (string) $productId));
        }

        $result = new CompletenessCalculationResult();
        $productIdentifier = $this->getProductIdentifierFromProductIdQuery->execute($productId);
        $completenessCollection = $this->completenessCalculator->fromProductIdentifier((string) $productIdentifier);

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
