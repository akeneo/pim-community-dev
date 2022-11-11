<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Import\Product;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * +---------+-----------------------+
 * | Locales |  Redactor |  Manager  |
 * +---------+-----------------------+
 * |  en_US  | View,Edit | View,Edit |
 * |  fr_FR  |   View    | View,Edit |
 * |  de_DE  |     -     | View,Edit |
 * +---------+-----------------------+
 *
 * +-------------------------------------------------------+-----------------------+
 * |                     Attributes                        |  Redactor |  Manager  |
 * +-------------------------------------------------------+-----------------------+
 * |  a_text (attributeGroupA)                             | View,Edit | View,Edit |
 * |  a_localized_and_scopable_text_area (attributeGroupA) | View,Edit | View,Edit |
 * |  a_number_integer (attributeGroupB)                   |   View    | View,Edit |
 * |  a_multi_select (attributeGroupC)                     |     -     | View,Edit |
 * +-------------------------------------------------------+-----------------------+
 */
class ImportProducsWithValuesIntegration extends AbstractProductImportTestCase
{
    public function testSuccessfullyToImportProductsWithEditableAttributeWithPermissions()
    {
        $importCSV = <<<CSV
sku;a_text
productA;a text
CSV;

        $expected = [
            'productA' => [
                'a_text' => 'a text'
            ]
        ];

        $this->assertAuthenticatedImport($importCSV, 'mary', $expected, 1, 0, 0);
    }

    public function testToImportProductsWithViewableAttributeWithPermissions()
    {
        $this->createProduct('productB', ['values' => [
            'a_text' => [['data' => 'simple text', 'locale' => null, 'scope' => null]],
            'a_number_integer' => [['data' => 20, 'locale' => null, 'scope' => null]]
        ]]);

        $importCSV = <<<CSV
sku;a_text;a_number_integer
productA;a text;12
productB;simple text;20
CSV;

        $expectedWarnings = [
            'Attribute "a_number_integer" belongs to the attribute group "attributeGroupB" on which you only have view permission.',
            'No product with identifier "productA" has been found'
        ];
        $this->assertAuthenticatedImport($importCSV, 'mary', [], 1, 0, 1, $expectedWarnings);
    }

    public function testToSkipImportProductsWithNotViewablableAttributeWithPermissions()
    {
        $importCSV = <<<CSV
sku;a_text;a_multi_select
productA;a text;optionA
CSV;

        $this->assertAuthenticatedImport($importCSV, 'mary', [], 0, 0, 0, [], BatchStatus::FAILED);
    }

    public function testSuccessfullyToImportProductsWithEditableLocalizableAttributeWithPermissions()
    {
        $importCSV = <<<CSV
sku;a_localized_and_scopable_text_area-en_US-tablet
productA;EN text
CSV;

        $expected = [
            'productA' => [
                'a_localized_and_scopable_text_area-en_US-tablet' => 'EN text'
            ]
        ];

        $this->assertAuthenticatedImport($importCSV, 'mary', $expected, 1, 0, 0);
    }

    public function testToImportProductsWithViewableLocalizableAttributeWithPermissions()
    {
        $importCSV = <<<CSV
sku;a_localized_and_scopable_text_area-fr_FR-tablet
productA;FR text
CSV;

        $expectedWarning = [
            'You only have a view permission on the locale "fr_FR".',
            'No product with identifier "productA" has been found'
        ];
        $this->assertAuthenticatedImport($importCSV, 'mary', [], 0, 0, 1, $expectedWarning);
    }

    public function testToSkipProductsWithNotViewablableLocalizableAttributeWithPermissions()
    {
        $importCSV = <<<CSV
sku;a_localized_and_scopable_text_area-de_DE-tablet
productA;DE text
CSV;

        $this->assertAuthenticatedImport($importCSV, 'mary', [], 0, 0, 0, [], BatchStatus::FAILED);
    }

    public function testSuccessfullyToUpdateADraft()
    {
        $productA = $this->createProduct('productA', ['categories' => ['categoryA'], 'values' => [
            'a_multi_select' => [['data' => ['optionA'], 'locale' => null, 'scope' => null]],
            'a_text' => [['data' => 'the text', 'locale' => null, 'scope' => null]],
            'a_localized_and_scopable_text_area' => [
                ['data' => 'FR text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ['data' => 'EN text', 'locale' => 'en_US', 'scope' => 'tablet'],
            ]
        ]]);
        $this->createEntityWithValuesDraft($productA, 'mary', ['values' => [
            'a_text' => [['data' => 'the simple text', 'locale' => null, 'scope' => null]],
            'a_localized_and_scopable_text_area' => [
                ['data' => 'French text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ['data' => 'English text', 'locale' => 'en_US', 'scope' => 'tablet'],
            ]
        ]]);
        $this->get('doctrine')->getManager()->refresh($productA);

        $importCSV = <<<CSV
sku;a_localized_and_scopable_text_area-en_US-tablet
productA;An english text
CSV;
        $this->jobLauncher->launchAuthenticatedSubProcessImport('csv_product_import', $importCSV, 'mary');
        $this->get('doctrine')->getManager()->clear();
        $this->assertSame(1, $this->countProduct());
        $this->assertSame(1, $this->countProductDraft());

        $changes = $this->getProductDraft($productA, 'mary')->getChanges()['values'];

        $expected = [
            'a_text' => [['data' => 'the simple text', 'locale' => null, 'scope' => null]],
            'a_localized_and_scopable_text_area' => [
                ['data' => 'French text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ['data' => 'An english text', 'locale' => 'en_US', 'scope' => 'tablet'],
            ]
        ];
        $this->assertEquals($expected, $changes);
    }

    public function testImportProductsWithUuid()
    {
        $productA = $this->createProduct('productA', ['categories' => ['categoryA'], 'values' => []]);
        $productB = $this->createProduct('productB', ['categories' => ['categoryA'], 'values' => [
            'a_text' => [['data' => 'the text', 'locale' => null, 'scope' => null]],
        ]]);

        $nonExistingUuid = Uuid::uuid4()->toString();
        $productAUuid = $productA->getUuid()->toString();
        $this->get('doctrine')->getManager()->refresh($productA);
        $this->get('doctrine')->getManager()->refresh($productB);

        $importCSV = <<<CSV
uuid;sku;a_text
{$productAUuid};newProductA;a_text
;productB;text_updated
{$nonExistingUuid};new_product;a_text
CSV;
        $this->jobLauncher->launchAuthenticatedSubProcessImport('csv_product_import', $importCSV, 'admin');
        $this->get('doctrine')->getManager()->clear();

        $updatedProductA = $this->getProduct('newProductA');
        $updatedProductB = $this->getProduct('productB');
        $productC = $this->getProduct('new_product');

        // product A should have new sku with same uuid
        Assert::assertNotNull($updatedProductA);
        Assert::assertEquals($updatedProductA->getUuid()->toString(), $productAUuid);

        // product B should be updated
        Assert::assertEquals($updatedProductB->getValue('a_text')->getData(), 'text_updated');

        // product C should have been created with given uuid
        Assert::assertNotNull($productC);
        Assert::assertEquals($productC->getUuid()->toString(), $nonExistingUuid);
    }

    public function testToImportProductWithoutIdentifier()
    {
        $nonExistingUuidToFail = Uuid::uuid4()->toString();
        $importCSV = <<<CSV
uuid;sku;a_text
{$nonExistingUuidToFail};;FR text
CSV;

        $this->assertAuthenticatedImport($importCSV, 'admin', [], 1, 0, 0);
    }
}
