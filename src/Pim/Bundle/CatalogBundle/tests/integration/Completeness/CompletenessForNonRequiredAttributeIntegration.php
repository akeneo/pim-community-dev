<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Filling in non required attributes should not have any impact on the completeness results.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessForNonRequiredAttributeIntegration extends AbstractCompletenessIntegration
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

        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('ecommerce', $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals($requiredCount, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
        $this->assertEquals(0, $completeness->getMissingAttributes()->count());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getMinimalCatalogPath()]);
    }
}
