<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\EditProductCommand;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\FillProductValuesCommand;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\Value;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\ValueCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ExistingAttributeValidatorIntegration extends TestCase
{
    public function test_existing_attribute_validator()
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

    public function test_not_existing_attribute_validator()
    {

        $valueCollection = new ValueCollection(
            [
                new Value('foo', null, null, 'file'),
                new Value('a_file', 'en_US', 'ecommerce', '2016-06-13T00:00:00+02:00')
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
