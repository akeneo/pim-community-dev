<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\CategorizeProductCommand;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\FillProductValuesCommand;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\Value;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\ValueCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ExistingCategoriesValidatorIntegration extends TestCase
{
    public function test_existing_categories_validator()
    {

        $command = new CategorizeProductCommand('identifier_product', ['categoryA', 'categoryA1']);

        $violations = $this->get('validator')->validate($command);
        Assert::assertCount(0, $violations);
    }

    //public function test_not_existing_attribute_validator()
    //{
    //
    //    $valueCollection = new ValueCollection(
    //        [
    //            new Value('foo', null, null, 'file'),
    //            new Value('a_file', 'en_US', 'ecommerce', '2016-06-13T00:00:00+02:00')
    //        ]
    //    );
    //
    //    $command = new FillProductValuesCommand('identifier_product', $valueCollection);
    //
    //    $violations = $this->get('validator')->validate($command);
    //    Assert::assertCount(1, $violations);
    //}

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
