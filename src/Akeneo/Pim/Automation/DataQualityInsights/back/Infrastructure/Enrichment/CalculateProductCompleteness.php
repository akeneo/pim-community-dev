<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdentifierFromProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;

final class CalculateProductCompleteness implements CalculateProductCompletenessInterface
{
    /** @var GetProductIdentifierFromProductIdQueryInterface */
    private $getProductIdentifierFromProductIdQuery;

    /** @var CompletenessCalculator */
    private $completenessCalculator;

    public function __construct(
        GetProductIdentifierFromProductIdQueryInterface $getProductIdentifierFromProductIdQuery,
        CompletenessCalculator $completenessCalculator
    ) {
        $this->completenessCalculator = $completenessCalculator;
        $this->getProductIdentifierFromProductIdQuery = $getProductIdentifierFromProductIdQuery;
    }

    public function calculate(ProductId $productId): CompletenessCalculationResult
    {
        $result = new CompletenessCalculationResult();
        $productIdentifier = $this->getProductIdentifierFromProductIdQuery->execute($productId);
        $completenessCollection = $this->completenessCalculator->fromProductIdentifier(strval($productIdentifier));

        foreach ($completenessCollection as $completeness) {
            $channelCode = new ChannelCode($completeness->channelCode());
            $localeCode = new LocaleCode($completeness->localeCode());
            $result->addRate($channelCode, $localeCode, new Rate($completeness->ratio()));
            $result->addMissingAttributes($channelCode, $localeCode, $completeness->missingAttributeCodes());
        }

        return $result;
    }
}
