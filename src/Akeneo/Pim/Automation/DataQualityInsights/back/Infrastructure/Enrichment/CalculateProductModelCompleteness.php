<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;

class CalculateProductModelCompleteness implements CalculateProductCompletenessInterface
{
    /** @var GetCompletenessProductMasks */
    private $getCompletenessProductMasks;

    /** @var GetProductModelAttributesMaskQueryInterface */
    private $getProductModelAttributesMaskQuery;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    public function __construct(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetProductModelAttributesMaskQueryInterface $getProductModelAttributesMaskQuery,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->getCompletenessProductMasks = $getCompletenessProductMasks;
        $this->getProductModelAttributesMaskQuery = $getProductModelAttributesMaskQuery;
        $this->productModelRepository = $productModelRepository;
    }

    public function calculate(ProductId $productModelId): CompletenessCalculationResult
    {
        $productMask = $this->getProductMask($productModelId);

        $requiredAttributesMask = $this->getProductModelAttributesMaskQuery->execute($productModelId);

        $result = new CompletenessCalculationResult();
        foreach ($productMask->completenessCollectionForProduct($requiredAttributesMask) as $completeness) {
            $channelCode = new ChannelCode($completeness->channelCode());
            $localeCode = new LocaleCode($completeness->localeCode());
            $result->addRate($channelCode, $localeCode, new Rate($completeness->ratio()));
            $result->addMissingAttributes($channelCode, $localeCode, $completeness->missingAttributeCodes());
        }

        return $result;
    }

    private function getProductMask(ProductId $productModelId): CompletenessProductMask
    {
        $productModel = $this->productModelRepository->find($productModelId->toInt());

        if (null === $productModel) {
            throw new \RuntimeException(sprintf('Unable to retrieve product model %d to calculate its completeness.', $productModelId->toInt()));
        }

        return $this->getCompletenessProductMasks->fromValueCollection(
            $productModel->getId(),
            $productModel->getCode(),
            $productModel->getFamily()->getCode(),
            $productModel->getValues()
        );
    }
}
