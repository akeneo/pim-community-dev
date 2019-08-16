<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Filling in non required attributes should not have any impact on the completeness results.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessForNonRequiredAttributeIntegration extends AbstractCompletenessTestCase
{
    public function testAttributeNotRequiredByFamily()
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $family->setCode('family_without_any_attribute_requirements');
        $this->get('pim_catalog.saver.family')->save($family);

        $this->createAttribute('a_text', AttributeTypes::TEXT);
        $this->createAttribute('a_number', AttributeTypes::NUMBER);

        $product = $this->createProductWithStandardValues(
            $family,
            'always_complete_product',
            [
                'values' => [
                    'a_text'   => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'This is some text',
                        ],
                    ],
                    'a_number' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($product, 1);
    }

    public function testAttributeRequiredByFamily()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT
        );

        $product = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Some text for ecommerce channel'
                        ],
                    ]
                ]
            ]
        );

        $this->assertComplete($product, 2);
    }

    /**
     * @param ProductInterface $product
     * @param int              $requiredCount
     *
     * @internal param string $localeCode
     */
    private function assertComplete(ProductInterface $product, $requiredCount)
    {
        $this->assertCompletenessesCount($product, 1);

        $completeness = $this->getCurrentCompleteness($product);

        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals('ecommerce', $completeness->channelCode());
        $this->assertEquals(100, $completeness->ratio());
        $this->assertEquals($requiredCount, $completeness->requiredCount());
        $this->assertEquals(0, $completeness->missingCount());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
