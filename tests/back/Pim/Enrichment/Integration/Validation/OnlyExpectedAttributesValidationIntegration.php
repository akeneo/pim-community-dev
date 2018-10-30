<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Validation;

use Akeneo\Test\Integration\TestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OnlyExpectedAttributesValidationIntegration extends TestCase
{
    public function testAttributeBelongsToFamilyValidation()
    {
        $variantProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('running-shoes-m-crimson-red');

        $data = [
            'values' => [
                'care_instructions' => [['locale' => null, 'scope' => null, 'data' => 'invalid']]
            ]
        ];

        $this->get('pim_catalog.updater.product')->update($variantProduct, $data);
        $violations = $this->get('pim_catalog.validator.product')->validate($variantProduct);

        $this->assertCount(1, $violations);
        $this->assertSame('Attribute "care_instructions" does not belong to the family "shoes"', $violations->get(0)->getMessage());
        $this->assertSame('attribute', $violations->get(0)->getPropertyPath());
    }

    public function testAttributeBelongsToAttributeSetValidation()
    {
        $variantProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('running-shoes-m-crimson-red');

        $data = [
            'values' => [
                'variation_name' => [['locale' => 'en_US', 'scope' => null, 'data' => 'invalid']]
            ]
        ];

        $this->get('pim_catalog.updater.product')->update($variantProduct, $data);
        $violations = $this->get('pim_catalog.validator.product')->validate($variantProduct);

        $this->assertCount(1, $violations);
        $this->assertSame('Cannot set the property "variation_name" to this entity as it is not in the attribute set', $violations->get(0)->getMessage());
        $this->assertSame('attribute', $violations->get(0)->getPropertyPath());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
