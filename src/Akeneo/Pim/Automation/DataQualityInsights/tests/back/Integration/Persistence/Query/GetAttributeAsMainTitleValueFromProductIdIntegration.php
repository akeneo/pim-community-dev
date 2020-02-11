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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetAttributeAsMainTitleValueFromProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

final class GetAttributeAsMainTitleValueFromProductIdIntegration extends TestCase
{
    /** @var GetAttributeAsMainTitleValueFromProductId */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetAttributeAsMainTitleValueFromProductId::class);
    }

    public function test_it_returns_attribute_as_main_title_if_exists()
    {
        $productId = $this->createProductWithAttributeAsMainLabel();

        $expectedResult = [
            'title' => [
                '<all_channels>' => [
                    'en_US' => 'some text'
                ],
            ],
        ];

        $result = $this->query->execute($productId);

        $this->assertSame($expectedResult, $result);
    }

    public function test_it_does_not_returns_attribute_as_main_title()
    {
        $productId = $this->createProductWithoutAttributeAsMainLabel();

        $expectedResult = [];

        $result = $this->query->execute($productId);

        $this->assertSame($expectedResult, $result);
    }

    private function createProductWithAttributeAsMainLabel()
    {
        $familyCode = $this->createFamily();

        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier('product_with_main_title_value')
            ->withFamily($familyCode)
            ->build();

        $data = [
            'values' => [
                'title' => [['scope' => null, 'locale' => 'en_US', 'data' => 'some text']],
            ]
        ];

        $this->get('pim_catalog.updater.product')->update($product, $data);

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId($product->getId());
    }

    private function createProductWithoutAttributeAsMainLabel()
    {
        $familyCode = $this->createFamily();

        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier('product_without_main_title_value')
            ->withFamily($familyCode)
            ->build();

        $data = [
            'values' => [
                'sku' => [['scope' => null, 'locale' => null, 'data' => 'some text']],
            ]
        ];

        $this->get('pim_catalog.updater.product')->update($product, $data);

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId($product->getId());
    }

    private function createFamily(): string
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => 'title',
            'type' => AttributeTypes::TEXT,
            'unique' => false,
            'group' => 'other',
            'localizable' => true
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => 'family',
                'attributes' => ['sku', 'title'],
                'attribute_as_label' => 'title',
            ]);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family->getCode();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
