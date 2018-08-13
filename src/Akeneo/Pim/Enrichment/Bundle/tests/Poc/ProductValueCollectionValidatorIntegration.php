<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Product;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ProductValueCollectionValidatorIntegration extends TestCase
{
    public function test_value_collection_validator()
    {
        $product = Product::fromApiFormat([
            'identifier' => 'identifier_product',
            'values' => [
                'a_file' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'file'
                    ],
                ],
                'a_date' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => '2016-06-13T00:00:00+02:00'
                    ]
                ]
            ]
        ]);

        $violations = $this->get('validator')->validate($product);
        Assert::assertCount(0, $violations);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
