<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateProductModelCompleteness implements \Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface
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
        $result = new CompletenessCalculationResult();
        $productMask = $this->getProductMask($productModelId);
        $requiredAttributesMask = $this->getProductModelAttributesMaskQuery->execute($productModelId);

        if (null === $productMask || null === $requiredAttributesMask) {
            return $result;
        }

        foreach ($productMask->completenessCollectionForProduct($requiredAttributesMask) as $completeness) {
            $channelCode = new ChannelCode($completeness->channelCode());
            $localeCode = new LocaleCode($completeness->localeCode());
            $result->addRate($channelCode, $localeCode, new Rate($completeness->ratio()));
            $result->addMissingAttributes($channelCode, $localeCode, $completeness->missingAttributeCodes());
        }

        return $result;
    }

    private function getProductMask(ProductId $productModelId): ?CompletenessProductMask
    {
        $productModel = $this->productModelRepository->find($productModelId->toInt());

        if (null === $productModel) {
            return null;
        }

        return $this->getCompletenessProductMasks->fromValueCollection(
            $productModel->getId(),
            $productModel->getCode(),
            $productModel->getFamily()->getCode(),
            $productModel->getValues()
        );
    }
}
