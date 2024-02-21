<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for scopable attribute types.
 *
 * We test from the minimal catalog that contains only one channel, with one locale activated.
 *
 * For each test, we create a family where the scopable attribute is required.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessForScopableAttributeIntegration extends AbstractCompletenessTestCase
{
    public function testCompleteScopable()
    {
        $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            false,
            true
        );

        $product = $this->createProductWithStandardValues(
            'another_product',
            [
                new SetFamily('another_family'),
                new SetTextValue('a_text', 'ecommerce', null, 'just a text'),
            ]
        );

        $this->assertComplete($product, 'ecommerce');
    }

    public function testNotCompleteScopable()
    {
        $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            false,
            true
        );

        $productWithoutValues = $this->createProductWithStandardValues(
            'product_witout_values',
            [new SetFamily('another_family')]
        );
        $this->assertNotComplete($productWithoutValues, 'ecommerce', ['a_text']);

        $productDataEmpty = $this->createProductWithStandardValues(
            'product_data_empty',
            [
                new SetFamily('another_family'),
                new ClearValue('a_text', 'ecommerce', null)
            ]
        );
        $this->assertNotComplete($productDataEmpty, 'ecommerce');
    }

    /**
     * @param ProductInterface $product
     * @param string           $channelCode
     */
    private function assertNotComplete(ProductInterface $product, string $channelCode)
    {
        $this->assertCompletenessesCount($product, 1);

        $completeness = $this->getCurrentCompleteness($product);

        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals($channelCode, $completeness->channelCode());
        $this->assertEquals(50, $completeness->ratio());
        $this->assertEquals(2, $completeness->requiredCount());
        $this->assertEquals(1, $completeness->missingCount());
    }

    /**
     * @param ProductInterface $product
     * @param string           $channelCode
     */
    private function assertComplete(ProductInterface $product, $channelCode)
    {
        $this->assertCompletenessesCount($product, 1);

        $completeness = $this->getCurrentCompleteness($product);

        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals($channelCode, $completeness->channelCode());
        $this->assertEquals(100, $completeness->ratio());
        $this->assertEquals(2, $completeness->requiredCount());
        $this->assertEquals(0, $completeness->missingCount());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
    }
}
