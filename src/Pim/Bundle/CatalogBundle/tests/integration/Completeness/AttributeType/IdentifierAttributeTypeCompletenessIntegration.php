<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for identifier attribute type.
 * The family that is created here has the "sku" (and ONLY the "sku") as required attribute.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IdentifierAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
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
        $completenesses = $product->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(1, $completenesses);

        $completeness = current($completenesses);

        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('ecommerce', $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals(1, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
    }
}
