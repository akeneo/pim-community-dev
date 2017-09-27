<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\ConnectorBundle\tests\integration\Import\Product;

/**
 * +--------------+-----------------------------------+
 * |  Categories  |     Redactor    |     Manager     |
 * +--------------+-----------------------------------+
 * |    master    | View,Edit,Owner | View,Edit,Owner |
 * |  categoryA   |    View,Edit    | View,Edit,Owner |
 * |  categoryA1  |      View       | View,Edit,Owner |
 * |  categoryB   |        -        | View,Edit,Owner |
 * +--------------+-----------------------------------+
 */
class ImportProductsWithCategoriesIntegration extends AbstractProductImportTestCase
{
    /**
     * Whatever the categories, if product is new, it's always created.
     */
    public function testSuccessfullyToCreateProductsWhateverTheCategoryWithPermissions()
    {
        $this->assertSame(0, $this->countProduct());

        $importCSV = <<<CSV
sku;categories;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;master;Product A;English description
productB;categoryA1;Product B;English description
productC;categoryA;Product B;English description
CSV;

        $this->assertImport('pim:batch:job', $importCSV, 'mary', [], 3, 0, 0);
    }

    public function testSuccessfullyToUpdateProductsWithCategoryIOwnWithPermissions()
    {
        $this->createProduct('productA', ['categories' => ['master', 'categoryA1']]);

        $importCSV = <<<CSV
sku;categories;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;master;Product A;English description
CSV;

        $expected = [
            'productA' => [
                'a_text' => 'Product A',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => 'English description'
            ]
        ];

        $this->assertImport('pim:batch:job', $importCSV, 'mary', $expected, 1, 0, 0);
    }

    public function testSuccessfullyToUpdateProductsWithEditableCategoryWithPermissions()
    {
        $productA = $this->createProduct('productA', ['enabled' => true, 'categories' => ['categoryA']]);

        $importCSV = <<<CSV
sku;enabled;family;groups;categories;X_SELL-products;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;1;;;categoryA;;My text;English description
CSV;

        $this->assertImport('pim:batch:job', $importCSV, 'mary', [], 1, 1, 0);

        $changes = $this->getProductDraft($productA, 'mary')->getChanges()['values'];
        $this->assertSame('My text', $changes['a_text'][0]['data']);
        $this->assertSame('English description', $changes['a_localized_and_scopable_text_area'][0]['data']);
    }

    public function testToSkipProductsWithNotViewableCategoriesWithPermissions()
    {
        $this->createProduct('productA', ['categories' => ['categoryB'], 'values' => [
            'a_text' => [['data' => 'my text', 'locale' => null, 'scope' => null]]
        ]]);

        $importCSV = <<<CSV
sku;categories;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;categoryA1;Product A;English description for ecommerce
CSV;

        $expected = [
            'productA' => [
                'a_text' => 'my text',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => null
            ]
        ];

        $expectedWarnings = [
            'You can neither view, nor update, nor delete the product "productA", as it is only categorized in categories on which you do not have a view permission.'
        ];

        $this->assertImport('pim:batch:job', $importCSV, 'mary', $expected, 1, 0, 1, $expectedWarnings);
    }

    /**
     * A product in a viewable category can be imported only if data do not changed
     */
    public function testSuccessfullyToImportProductWithViewableCategoryWithPermissions()
    {
        $this->createProduct('productA', ['categories' => ['categoryA1'], 'values' => [
            'a_text' => [['data' => 'my text', 'locale' => null, 'scope' => null]]
        ]]);
        $this->createProduct('productB', ['categories' => ['categoryA1'], 'values' => [
            'a_text' => [['data' => 'a simple text', 'locale' => null, 'scope' => null]]
        ]]);

        $importCSV = <<<CSV
sku;categories;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;categoryA1;my text;
productB;categoryA1;An other text;English text
CSV;

        $expected = [
            'productA' => [
                'a_text' => 'my text',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => null
            ],
            'productB' => [
                'a_text' => 'a simple text',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => null
            ]
        ];

        $expectedWarnings = [
            'Product "productB" cannot be updated. It should be at least in an own category.',
        ];

        $this->assertImport('pim:batch:job', $importCSV, 'mary', $expected, 2, 0, 1, $expectedWarnings);
    }

    /**
     * ProductA will be saved because only values are modified
     * ProductB will be skipped because we try to update the "enabled" field
     * ProductC will be skipped because we try to update the "family" field
     * ProductD will be skipped because we try to update the "groups" field
     * ProductE will be skipped because we try to update the "associations" field
     * ProductE will be skipped because we try to update the "categories" field
     */
    public function testToSkipProductDraftWhenFieldsAreUpdatedForRedactorWithPermissions()
    {
        $this->createProduct('productA', ['enabled' => true, 'categories' => ['categoryA']]);
        $this->createProduct('productB', ['enabled' => true, 'categories' => ['categoryA']]);
        $this->createProduct('productC', ['family' => 'familyA', 'categories' => ['categoryA']]);
        $this->createProduct('productD', ['groups' => ['groupA'], 'categories' => ['categoryA']]);
        $this->createProduct('productE', ['categories' => ['categoryA']]);
        $this->createProduct('productF', ['categories' => ['categoryA']]);

        $importCSV = <<<CSV
sku;enabled;family;groups;categories;X_SELL-products;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;1;;;categoryA;;My text;English description
productB;0;;;categoryA;;An other text;Description
productC;1;familyA1;;categoryA;;;
productD;1;;groupB;categoryA;;;
productE;1;;;categoryA;productA;;
productF;1;;;master;;;
CSV;

        $expectedWarnings = [
            'You cannot update the field "enabled". You should at least own this product to do it.',
            'You cannot update the field "family". You should at least own this product to do it.',
            'You cannot update the field "groups". You should at least own this product to do it.',
            'You cannot update the field "categories". You should at least own this product to do it.',
            'You cannot update the field "associations". You should at least own this product to do it.',
        ];

        $this->assertImport('pim:batch:job', $importCSV, 'mary', [], 6, 1, 5, $expectedWarnings);
    }

    public function testSuccessfullyToCreateProductsWhateverTheCategoryWithoutPermission()
    {
        $this->assertSame(0, $this->countProduct());

        $importCSV = <<<CSV
sku;categories;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;master;Product A;English description
productB;categoryA1;Product B;English description
productC;categoryA;Product C;English description
CSV;

        $expected = [
            'productA' => [
                'a_text' => 'Product A',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => 'English description'
            ],
            'productB' => [
                'a_text' => 'Product B',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => 'English description'
            ],
            'productC' => [
                'a_text' => 'Product C',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => 'English description'
            ],
        ];

        $this->assertImport('akeneo:batch:job', $importCSV, null, $expected, 3, 0, 0);
    }

    public function testSuccessfullyToUpdateProductsWithoutPermission()
    {
        $this->createProduct('productA', ['categories' => ['master']]);
        $this->createProduct('productB', ['categories' => ['categoryA']]);
        $this->createProduct('productC', ['categories' => ['categoryA1']]);
        $this->createProduct('productD', ['categories' => ['categoryB']]);

        $importCSV = <<<CSV
sku;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;Product A;English description
productB;Product B;description
productC;Product C;description
productD;Product D;description
CSV;

        $expected = [
            'productA' => [
                'a_text' => 'Product A',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => 'English description'
            ],
            'productB' => [
                'a_text' => 'Product B',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => 'description'
            ],
            'productC' => [
                'a_text' => 'Product C',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => 'description'
            ],
            'productD' => [
                'a_text' => 'Product D',
                'a_localized_and_scopable_text_area-en_US-ecommerce' => 'description'
            ]
        ];

        $this->assertImport('akeneo:batch:job', $importCSV, null, $expected, 4, 0, 0);
    }

    public function testSuccessfullyToUpdateFieldWithoutPermission()
    {
        $this->createProduct('productA', ['enabled' => true, 'categories' => ['categoryA']]);
        $this->createProduct('productB', ['enabled' => true, 'categories' => ['categoryA']]);
        $this->createProduct('productC', ['family' => 'familyA', 'categories' => ['categoryA']]);
        $this->createProduct('productD', ['groups' => ['groupA'], 'categories' => ['categoryA']]);
        $this->createProduct('productE', ['categories' => ['categoryA']]);
        $this->createProduct('productF', ['categories' => ['categoryA']]);

        $importCSV = <<<CSV
sku;enabled;family;groups;categories;X_SELL-products;a_text;a_localized_and_scopable_text_area-en_US-ecommerce
productA;1;;;categoryA;;My text;English description
productB;0;;;categoryA;;An other text;Description
productC;1;familyA1;;categoryA;;;
productD;1;;groupB;categoryA;;;
productE;1;;;categoryA;productA;;
productF;1;;;master;;;
CSV;

        $this->assertImport('akeneo:batch:job', $importCSV, null, [], 6, 0, 0);
    }
}
