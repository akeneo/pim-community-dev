<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\EditProductCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ExistingCategoriesValidatorIntegration extends TestCase
{
    public function test_existing_categories_validator()
    {

        $command = new EditProductCommand('identifier_product', ['categoryA', 'categoryA1'], null);

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
