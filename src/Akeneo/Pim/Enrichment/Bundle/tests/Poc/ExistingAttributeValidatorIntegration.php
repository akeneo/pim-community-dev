<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;

class ExistingAttributeValidatorIntegration extends TestCase
{
    public function test_existing_attribute_validator()
    {
        $attributeToRemove = $this->get('attribute.validator')->validate()->;

    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
