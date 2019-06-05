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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductInfosForSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectProductInfosForSubscriptionQuery;
use Akeneo\Test\Integration\TestCase;

class SelectProductInfosForSubscriptionQueryIntegration extends TestCase
{
    public function test_it_returns_the_information_of_an_unsubscribed_variant_product()
    {
        $productId = $this->createProductVariant('akeneo_mug');

        $expectedProductInfos = new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, []),
            new Family(new FamilyCode('familyA1'), ['en_US' => 'A family A1']),
            'akeneo_mug',
            true,
            false
        );
        $productInfos = $this->getQuery()->execute($productId);

        $this->assertEquals($expectedProductInfos, $productInfos);
    }

    public function test_it_returns_the_information_of_a_subscribed_product()
    {
        $this->createTextAttribute('asin');
        $this->createTextAttribute('ean');
        $this->createIdentifiersMapping();
        $productId = $this->createProduct('akeneo_mug', [
            'asin' => 'ASIN234',
            'ean' => '123456',
        ]);
        $this->createProduct('random_mug');
        $this->createProductSubscription($productId);

        $expectedProductInfos = new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, ['asin' => 'ASIN234', 'upc' => '123456']),
            new Family(new FamilyCode('familyA'), ['en_US' => 'A family A', 'fr_FR' => 'Une famille A']),
            'akeneo_mug',
            false,
            true
        );
        $productInfos = $this->getQuery()->execute($productId);

        $this->assertEquals($expectedProductInfos, $productInfos);
    }

    public function test_it_returns_the_information_of_a_product_without_family()
    {
        $productId = $this->createProductWithoutFamily('akeneo_mug');
        $expectedProductInfos = new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, []),
            null,
            'akeneo_mug',
            false,
            false
        );
        $productInfos = $this->getQuery()->execute($productId);

        $this->assertEquals($expectedProductInfos, $productInfos);
    }

    public function it_returns_null_if_the_product_does_not_exist()
    {
        $this->createProduct('akeneo_mug');

        $productInfos = $this->getQuery()->execute(new ProductId(42));

        $this->assertNull($productInfos);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductVariant(string $identifier): ProductId
    {
        $this->createProductModel('modelA');

        $product = $this->getFromTestContainer('pim_catalog.builder.product')->createProduct($identifier, 'familyA1');
        $this->getFromTestContainer('validator')->validate($product);
        $this->getFromTestContainer('pim_catalog.updater.product')->update($product, ['parent' => 'modelA']);
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return new ProductId($product->getId());
    }

    private function createProduct(string $identifier, array $values = []): ProductId
    {
        $builder = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')
            ->withFamily('familyA')
            ->withIdentifier($identifier);

        foreach ($values as $attrCode => $data) {
            $builder->withValue($attrCode, $data);
        }

        $product = $builder->build();
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return new ProductId($product->getId());
    }

    private function createProductWithoutFamily(string $identifier): ProductId
    {
        $builder = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier);

        $product = $builder->build();
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return new ProductId($product->getId());
    }

    private function createProductSubscription(ProductId $productId): void
    {
        $productSubscription = new ProductSubscription($productId, new SubscriptionId('a-random-string'), []);

        $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.product_subscription')->save($productSubscription);
    }

    private function createProductModel(string $identifier): void
    {
        $productModel = $this->getFromTestContainer('pim_catalog.factory.product_model')->create();
        $this->getFromTestContainer('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $identifier,
                'family_variant' => 'familyVariantA1'
            ]
        );

        $this->getFromTestContainer('pim_catalog.saver.product_model')->save($productModel);
    }

    private function createTextAttribute(string $code): void
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => 'pim_catalog_text',
                'group' => 'other',
            ]
        );
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createIdentifiersMapping(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
            ->find()
            ->map('asin', new AttributeCode('asin'))
            ->map('upc', new AttributeCode('ean'));

        $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')->save($mapping);
    }

    private function getQuery(): SelectProductInfosForSubscriptionQuery
    {
        return $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.product.select_product_infos_for_subscription');
    }
}
