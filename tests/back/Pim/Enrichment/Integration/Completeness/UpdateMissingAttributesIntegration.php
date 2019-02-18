<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Tests if missing attributes are well updated after product update.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateMissingAttributesIntegration extends AbstractCompletenessTestCase
{
    public function testMissingAttributesAreUpdated()
    {
        $productRepository = $this->get('pim_catalog.repository.product');
        $this->createFamilyWithRequirement('shoes', 'ecommerce', 'description', AttributeTypes::TEXT);
        $familyShoes = $this->createFamilyWithRequirement('shoes', 'ecommerce', 'item_story', AttributeTypes::TEXT);

        $pimpedShoes = $this->createProductWithStandardValues(
            $familyShoes,
            'pimped_shoes',
            [
                'values' => [
                    'description' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Pimped shoes you will love.'
                        ],
                    ]
                ]
            ]
        );

        $product = $productRepository->findOneByIdentifier('pimped_shoes');
        $completeness = $this->getCurrentCompleteness($pimpedShoes);
        $this->assertMissingAttributeCodes($completeness, ['item_story']);

        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'values' => [
                    'item_story' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'See this man wearing these beautiful pimped shoes, he looks so cool!'
                        ],
                    ]
                ]
            ]
        );

        $violations = $this->get('validator')->validate($product);
        $this->assertCount(0, $violations);
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $productRepository->findOneByIdentifier('pimped_shoes');
        $completeness = $this->getCurrentCompleteness($product);
        $this->assertMissingAttributeCodes($completeness, []);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
