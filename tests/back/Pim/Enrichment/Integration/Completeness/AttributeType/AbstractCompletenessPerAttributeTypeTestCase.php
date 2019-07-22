<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness\AttributeType;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use AkeneoTest\Pim\Enrichment\Integration\Completeness\AbstractCompletenessTestCase;

/**
 * Abstract class to check that the completeness has been well calculated for each attribute type of the PIM.
 *
 * We test from the minimal catalog that contains only one channel with one activated locale.
 * For each attribute type, we create an attribute. Then, we create a family where the attribute is required.
 * We create two products of this family, one with the required attribute filled in, the other without.
 * Finally we test the completeness calculation of those two products.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCompletenessPerAttributeTypeTestCase extends AbstractCompletenessTestCase
{
    /**
     * Here, the identifier and the attribute should be filled in.
     * Which means, there should be 0 missing, and 2 required.
     *
     * @param ProductInterface $product
     */
    protected function assertComplete(ProductInterface $product)
    {
        $this->assertCompletenessesCount($product, 1);

        $completeness = $this->getCurrentCompleteness($product);
        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals('ecommerce', $completeness->channelCode());
        $this->assertEquals(100, $completeness->ratio());
        $this->assertEquals(2, $completeness->requiredCount());
        $this->assertEquals(0, count($completeness->missingAttributeCodes()));
    }

    /**
     * Here, only the identifier should be filled in.
     * Which means, there should be 1 missing, and 2 required.
     *
     * @param ProductInterface $product
     */
    protected function assertNotComplete(ProductInterface $product)
    {
        $this->assertCompletenessesCount($product, 1);

        $completeness = $this->getCurrentCompleteness($product);
        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals('ecommerce', $completeness->channelCode());
        $this->assertEquals(50, $completeness->ratio());
        $this->assertEquals(2, $completeness->requiredCount());
        $this->assertEquals(1, count($completeness->missingAttributeCodes()));
    }

    /**
     * @param ProductInterface $product
     * @param string[]         $expectedAttributeCodes
     */
    protected function assertMissingAttributeForProduct(ProductInterface $product, array $expectedAttributeCodes)
    {
        $completeness = $this->getCurrentCompleteness($product);

        $this->assertMissingAttributeCodes($completeness, $expectedAttributeCodes);
    }
}
