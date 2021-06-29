<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment\GetProductModelAttributesMaskQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateProductModelCompletenessSpec extends ObjectBehavior
{
    public function let(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetProductModelAttributesMaskQueryInterface $getProductModelAttributesMaskQuery,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith($getCompletenessProductMasks, $getProductModelAttributesMaskQuery, $productModelRepository);
    }

    public function it_returns_an_empty_result_when_there_is_no_product_mask_to_apply(
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $productModelRepository->find(42)->willReturn(null);

        $this->calculate(new ProductId(42))->shouldBeLike(new CompletenessCalculationResult());
    }

    public function it_returns_an_empty_result_when_there_is_no_attributes_mask_to_apply(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetProductModelAttributesMaskQueryInterface $getProductModelAttributesMaskQuery,
        ProductModelRepositoryInterface $productModelRepository,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        WriteValueCollection $values,
        CompletenessProductMask $productMask
    ) {
        $productModelId = new ProductId(42);

        $productModelRepository->find(42)->willReturn($productModel);
        $productModel->getId()->willReturn(42);
        $productModel->getCode()->willReturn('a_product_model');
        $productModel->getFamily()->willReturn($family);
        $productModel->getValues()->willReturn($values);
        $family->getCode()->willReturn('a_family');

        $getCompletenessProductMasks->fromValueCollection(42, 'a_product_model', 'a_family', $values)->willReturn($productMask);

        $getProductModelAttributesMaskQuery->execute($productModelId)->willReturn(null);

        $this->calculate($productModelId)->shouldBeLike(new CompletenessCalculationResult());
    }
}
