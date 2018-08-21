<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\FillProductValuesCommand;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\Value;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Product;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ProductValueCollectionValidatorIntegration extends TestCase
{
    public function test_value_collection_validator()
    {
        $valueCollection = new ValueCollection(
            [
                new Value('a_text', 'en_US', 'ecommerce', 'text')
            ]
        );

        $command = new FillProductValuesCommand('identifier_product', $valueCollection);

        $violations = $this->get('validator')->validate($command);
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
