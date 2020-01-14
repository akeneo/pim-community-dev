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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetAttributesByTypeFromProductQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetLocalizableAttributesByTypeFromProductQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

class GetLocalizableAttributesByProductAndTypeQueryIntegration extends TestCase
{
    public function test_it_gets_product_localizable_attributes_by_type()
    {
        $productId = $this->createProduct();

        $expectedAttributeCodes = ['a_localized_and_scopable_text_area'];

        $result = $this
            ->get(GetLocalizableAttributesByTypeFromProductQuery::class)
            ->execute($productId, AttributeTypes::TEXTAREA);

        $this->assertSame($expectedAttributeCodes, $result);
    }

    private function createProduct(): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier('product_with_family')
            ->withFamily('familyA3')
            ->build();
        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
