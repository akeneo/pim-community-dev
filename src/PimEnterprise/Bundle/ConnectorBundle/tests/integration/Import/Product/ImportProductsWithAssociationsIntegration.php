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
class ImportProductsWithAssociationsIntegration extends AbstractProductImportTestCase
{
    public function testSuccessfullyToAddAssociationsWithPermissions()
    {
        $this->createProduct('productA', []);
        $this->createProduct('productB', ['categories' => ['categoryA1']]);
        $this->createProduct('productC', ['categories' => ['categoryA']]);

        $importCSV = <<<CSV
sku;X_SELL-products
productA;productB
CSV;

        $this->assertAuthenticatedImport($importCSV, 'mary', [], 3, 0, 0);
    }

    /**
     * ProductA will be skipped because productB is in a not viewable category
     * ProductC will be skipped because it's only viewable and we cannot update data
     * ProductD will be skipped because it's a draft and we cannot update associations on draft
     */
    public function testToSkipProductWithAssociationsWithPermissions()
    {
        $this->createProduct('productB', ['categories' => ['categoryB']]);
        $this->createProduct('productA', []);
        $this->createProduct('productC', ['categories' => ['categoryA1']]);
        $this->createProduct('productD', ['categories' => ['categoryA']]);

        $importCSV = <<<CSV
sku;X_SELL-products
productA;productB
productC;productA
productD;productA
CSV;

        $expectedWarnings = [
            'You cannot associate a product on which you have not a view permission.',
            'Product "productC" cannot be updated. It should be at least in an own category.',
            'You cannot update the field "associations". You should at least own this product to do it.',
        ];

        $this->assertAuthenticatedImport($importCSV, 'mary', [], 4, 0, 3, $expectedWarnings);
    }

    public function testSuccessfullyToUpdateProductWithAssociationsWithoutPermission()
    {
        $this->createProduct('productB', ['categories' => ['categoryB']]);
        $this->createProduct('productA', []);
        $this->createProduct('productC', ['categories' => ['categoryA1']]);
        $this->createProduct('productD', ['categories' => ['categoryA']]);

        $importCSV = <<<CSV
sku;X_SELL-products
productA;productB
productB;productA
productC;productA
CSV;

        $this->assertImport( $importCSV, null, [], 4, 0, 0);
    }
}
