<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Import;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ImportProductsIntegration extends AbstractImportProductTestCase
{
    /** @test */
    public function i_can_create_a_product_with_neither_a_uuid_nor_an_identifier(): void
    {
        $content = <<<CSV
            uuid;sku;family;enabled
            ;;familyA1;0
            CSV;
        $this->lauchImport($content);
        $this->assertImportedProduct(1, 0, 0);

        $products = $this->getProductRepository()->findBy(['identifier' => null, 'enabled' => false]);
        Assert::assertCount(1, $products);
        Assert::assertNotEquals(self::UUID_EMPTY_IDENTIFIER, $products[0]->getUuid()->toString());
        Assert::assertSame('familyA1', $products[0]->getFamily()->getCode());
    }

    /** @test */
    public function i_can_create_a_product_with_an_identifier_and_no_uuid(): void
    {
        $content = <<<CSV
            uuid;sku;family;enabled
            ;sku4;familyA;0
            CSV;
        $this->lauchImport($content);
        $this->assertImportedProduct(1, 0, 0);

        $product = $this->getProductRepository()->findOneByIdentifier('sku4');
        Assert::assertInstanceOf(Product::class, $product);
        Assert::assertFalse($product->isEnabled());
        Assert::assertSame('familyA', $product->getFamily()->getCode());
    }

    /** @test */
    public function i_can_create_a_product_with_a_uuid_and_an_identifier(): void
    {
        $content = <<<CSV
            uuid;sku;family;enabled
            7430d45e-a940-4ef3-83f2-732a5fa26663;sku4;familyA;0
            CSV;
        $this->lauchImport($content);
        $this->assertImportedProduct(1, 0, 0);

        $product = $this->getProductRepository()->find('7430d45e-a940-4ef3-83f2-732a5fa26663');
        Assert::assertInstanceOf(Product::class, $product);
        Assert::assertSame('sku4', $product->getIdentifier());
        Assert::assertFalse($product->isEnabled());
        Assert::assertSame('familyA', $product->getFamily()->getCode());
    }

    /** @test */
    public function i_can_create_a_product_with_a_uuid_but_no_identifier(): void
    {
        $content = <<<CSV
            uuid;sku;family;enabled
            7430d45e-a940-4ef3-83f2-732a5fa26663;;familyA;0
            CSV;
        $this->lauchImport($content);
        $this->assertImportedProduct(1, 0, 0);

        $product = $this->getProductRepository()->find('7430d45e-a940-4ef3-83f2-732a5fa26663');
        Assert::assertInstanceOf(Product::class, $product);
        Assert::assertNull($product->getIdentifier());
        Assert::assertFalse($product->isEnabled());
        Assert::assertSame('familyA', $product->getFamily()->getCode());
    }

    /** @test */
    public function i_can_update_a_product_with_its_uuid(): void
    {
        $uuid = self::UUID_SKU1;
        $content = <<<CSV
            uuid;sku;enabled;a_text
            {$uuid};sku1;0;A new text value
            CSV;
        $this->lauchImport($content);
        $this->assertImportedProduct(0, 1, 0);

        $product = $this->getProductRepository()->find(self::UUID_SKU1);
        Assert::assertInstanceOf(Product::class, $product);
        Assert::assertSame('sku1', $product->getIdentifier());
        Assert::assertFalse($product->isEnabled());
        Assert::assertSame('A new text value', $product->getValue('a_text')?->getData());
    }

    /** @test */
    public function i_can_update_a_product_with_its_identifier_if_uuid_is_not_defined(): void
    {
        $content = <<<CSV
            uuid;sku;enabled;a_text
            ;sku1;0;A new text value
            CSV;
        $this->lauchImport($content);

        $product = $this->getProductRepository()->find(self::UUID_SKU1);
        Assert::assertInstanceOf(Product::class, $product);
        Assert::assertSame('sku1', $product->getIdentifier());
        Assert::assertFalse($product->isEnabled());
        Assert::assertSame('A new text value', $product->getValue('a_text')?->getData());
    }

    /** @test */
    public function i_can_update_the_identifier_of_a_product_with_its_uuid(): void
    {
        $uuid1 = self::UUID_SKU1;
        $uuid2 = self::UUID_SKU2;
        $uuid3 = self::UUID_EMPTY_IDENTIFIER;

        $content = <<<CSV
            uuid;sku;enabled;a_text
            {$uuid1};sku4;0;A new text value
            {$uuid2};;0;Another new text value
            {$uuid3};a_new_sku;0;Yet another new text value
            CSV;
        $this->lauchImport($content);
        $this->assertImportedProduct(0, 3, 0);

        $product = $this->getProductRepository()->find(self::UUID_SKU1);
        Assert::assertInstanceOf(Product::class, $product);
        Assert::assertSame('sku4', $product->getIdentifier());
        Assert::assertFalse($product->isEnabled());
        Assert::assertSame('A new text value', $product->getValue('a_text')?->getData());

        $product = $this->getProductRepository()->find(self::UUID_SKU2);
        Assert::assertInstanceOf(Product::class, $product);
        Assert::assertNull($product->getIdentifier());
        Assert::assertFalse($product->isEnabled());
        Assert::assertSame('Another new text value', $product->getValue('a_text')?->getData());

        $product = $this->getProductRepository()->find(self::UUID_EMPTY_IDENTIFIER);
        Assert::assertInstanceOf(Product::class, $product);
        Assert::assertSame('a_new_sku', $product->getIdentifier());
        Assert::assertFalse($product->isEnabled());
        Assert::assertSame('Yet another new text value', $product->getValue('a_text')?->getData());
    }
}
