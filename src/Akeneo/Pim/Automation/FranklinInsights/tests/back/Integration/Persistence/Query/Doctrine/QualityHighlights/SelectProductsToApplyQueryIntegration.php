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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Product;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\TestCase;

class SelectProductsToApplyQueryIntegration extends TestCase
{
    public function test_it_selects_products_to_apply()
    {
        $this->createAttribute([
            'code' => 'description',
            'type' => AttributeTypes::TEXT,
            'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            'localizable' => true,
            'scopable' => true,
        ]);

        $this->createFamily(['sku', 'description']);

        $productId = $this->createProduct('product_1', [
            'description' => [
                [
                    'data' => 'Product 1',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ]);

        $products = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.quality_highlights.select_products_to_apply')->execute([$productId]);

        $this->assertEquals([
            new Product(
                new ProductId($productId),
                new FamilyCode('mugs'),
                [
                    'sku' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'product_1',
                        ]
                    ],
                    'description' => [
                        'ecommerce' => [
                            'en_US' => 'Product 1',
                        ],
                    ],
                ]
            )
        ], $products);

        $this->assertTrue(true);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(array $attributeProperties): void
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build($attributeProperties);

        $this->getFromTestContainer('validator')->validate($attribute);
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(array $attributeCodes): void
    {
        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => 'mugs',
                'attributes' => $attributeCodes,
                'labels' => []
            ]);

        $this->getFromTestContainer('validator')->validate($family);
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);
    }

    private function createProduct(string $identifier, array $productValues): int
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, 'mugs');

        $this->get('pim_catalog.updater.product')->update($product, ['values' => $productValues]);
        $this->get('validator')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product->getId();
    }
}
