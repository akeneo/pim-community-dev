<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness\AttributeType;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * Checks that the completeness has been well calculated for identifier attribute type.
 * The family that is created here has the "sku" (and ONLY the "sku") as required attribute.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IdentifierAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteIdentifier()
    {
        $family = $this->createFamily('another_family');

        $productCompleteWithIdentifier = $this->createProductWithStandardValues(
            $family,
            'product_complete_with_identifier'
        );

        $productCompleteWithIdentifierUpdated = $this->createProductWithStandardValues(
            $family,
            'product_complete_with_identifier_updated',
            [
                'values' => [
                    'sku' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'foo',
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productCompleteWithIdentifier);
        $this->assertComplete($productCompleteWithIdentifierUpdated);
    }

    /**
     * @param string $familyCode
     *
     * @return FamilyInterface
     */
    private function createFamily($familyCode)
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $family->setCode($familyCode);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * Here, the "sku" should be filled in.
     * Which means, there should be 0 missing, and 1 required.
     *
     * @param ProductInterface $product
     */
    protected function assertComplete(ProductInterface $product)
    {
        $completenesses = $this->getProductCompletenesses()->fromProductId($product->getId());

        $this->assertCount(1, $completenesses);

        /** @var ProductCompletenessWithMissingAttributeCodes $completeness */
        $completeness = $completenesses->getIterator()->current();

        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals('ecommerce', $completeness->channelCode());
        $this->assertEquals(100, $completeness->ratio());
        $this->assertEquals(1, $completeness->requiredCount());
        $this->assertEquals(0, $completeness->missingCount());
    }
}
