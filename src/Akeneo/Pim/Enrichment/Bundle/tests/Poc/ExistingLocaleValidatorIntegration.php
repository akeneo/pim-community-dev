<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\EditProductCommand;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\FillProductValuesCommand;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\Value;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Product;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ExistingLocaleValidatorIntegration extends TestCase
{
    public function test_existing_locale_validator()
    {
        $valueCollection = new ValueCollection(
            [
                new Value('a_file', null, null, 'file'),
                new Value('a_date', 'en_US', 'ecommerce', '2016-06-13T00:00:00+02:00')
            ]
        );

        $command = new EditProductCommand('identifier_product', null, $valueCollection);

        $violations = $this->get('validator')->validate($command);
        Assert::assertCount(0, $violations);
    }

    public function test_not_existing_locale_validator()
    {
        $valueCollection = new ValueCollection(
            [
                new Value('a_file', null, null, 'file'),
                new Value('a_date', 'foo', 'ecommerce', '2016-06-13T00:00:00+02:00')
            ]
        );

        $command = new EditProductCommand('identifier_product', null, $valueCollection);

        $violations = $this->get('validator')->validate($command);
        Assert::assertCount(1, $violations);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
