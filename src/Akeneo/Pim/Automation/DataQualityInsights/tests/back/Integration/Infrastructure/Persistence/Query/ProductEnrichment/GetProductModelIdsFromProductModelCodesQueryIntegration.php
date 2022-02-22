<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment\GetProductModelIdsFromProductModelCodesQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelIdsFromProductModelCodesQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_product_model_ids_by_product_model_codes(): void
    {
        $this->createMinimalFamilyAndFamilyVariant('family_V', 'family_V_1');
        $productModelIdA = $this->createProductModel('product_model_A', 'family_V_1')->getId();
        $productModelIdB = $this->createProductModel('product_model_B', 'family_V_1')->getId();
        $this->createProductModel('product_model_C', 'family_V_1')->getId();

        $productModelIds = $this->get(GetProductModelIdsFromProductModelCodesQuery::class)->execute(['product_model_A', 'product_model_B', 'unknown_product_model']);
        $expectedProductIds = [
            'product_model_A' => new ProductId($productModelIdA),
            'product_model_B' => new ProductId($productModelIdB),
        ];

        $this->assertEquals($expectedProductIds, $productModelIds);
    }
}
