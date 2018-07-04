<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for localisable and locale specific attribute types.
 *
 * We test from the minimal catalog that contains only 1 channel. The locales fr_FR and en_US are activated.
 *
 * The completeness calculation is tested for:
 *      - 1 localisable attribute
 *      - 1 locale specific attribute
 *
 * For each test, we create a family where the attribute is required.
 * Then, we create two products of this family, one with the required attribute filled in, the other without.
 * Finally we test the completeness calculation of those two products.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessForLocalisableAttributeIntegration extends AbstractCompletenessTestCase
{
    public function testLocalisable()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            true
        );

        $product = $this->createProductWithStandardValues(
            $family,
            'another_product',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => 'en_US',
                            'scope'  => null,
                            'data'   => 'just a text'
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => null
                        ],
                    ]
                ]
            ]
        );

        $this->assertComplete($product, 'en_US', 2);
        $this->assertNotComplete($product, 'fr_FR', 2, ['a_text']);
    }

    public function testNotCompleteLocaleSpecificNoLocale()
    {
        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            true,
            false,
            [$fr]
        );

        $productLocaleSpecificNoLocale = $this->createProductWithStandardValues(
            $family,
            'product_locale_specific_no_locale'
        );
        $this->assertNotComplete($productLocaleSpecificNoLocale, 'fr_FR', 2, ['a_text']);
        $this->assertComplete($productLocaleSpecificNoLocale, 'en_US', 1);
    }

    public function testNotCompleteLocaleSpecificLocaleEmpty()
    {
        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            true,
            false,
            [$fr]
        );

        $productLocaleSpecificLocaleEmpty = $this->createProductWithStandardValues(
            $family,
            'product_locale_specific_locale_empty',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => null
                        ],
                    ]
                ]
            ]
        );
        $this->assertNotComplete($productLocaleSpecificLocaleEmpty, 'fr_FR', 2, ['a_text']);
        $this->assertComplete($productLocaleSpecificLocaleEmpty, 'en_US', 1);
    }

    public function testCompleteLocaleSpecific()
    {
        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            true,
            false,
            [$fr]
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => 'juste un texte'
                        ],
                    ]
                ]
            ]
        );
        $this->assertComplete($productComplete, 'fr_FR', 2);
        $this->assertComplete($productComplete, 'en_US', 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');
        $ecommerce = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $ecommerce->addLocale($fr);
        $this->get('pim_catalog.saver.channel')->save($ecommerce);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param ProductInterface $product
     * @param string           $localeCode
     *
     * @throws \Exception
     * @return CompletenessInterface
     */
    private function getCompletenessByLocaleCode(ProductInterface $product, $localeCode)
    {
        $completenesses = $product->getCompletenesses()->toArray();

        foreach ($completenesses as $completeness) {
            if ($localeCode === $completeness->getLocale()->getCode()) {
                return $completeness;
            }
        }

        throw new \Exception(sprintf('No completeness for the locale "%s"', $localeCode));
    }

    /**
     * @param ProductInterface $product
     * @param string           $localeCode
     * @param int              $requiredCount
     * @param array            $expectedAttributeCodes
     */
    private function assertNotComplete(
        ProductInterface $product,
        $localeCode,
        $requiredCount,
        array $expectedAttributeCodes
    ) {
        $this->assertCompletenessesCount($product, 2);

        $completeness = $this->getCompletenessByLocaleCode($product, $localeCode);

        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals($localeCode, $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('ecommerce', $completeness->getChannel()->getCode());
        $this->assertEquals(50, $completeness->getRatio());
        $this->assertEquals($requiredCount, $completeness->getRequiredCount());
        $this->assertEquals(1, $completeness->getMissingCount());
        $this->assertMissingAttributeCodes($completeness, $expectedAttributeCodes);
    }

    /**
     * @param ProductInterface $product
     * @param string           $localeCode
     * @param int              $requiredCount
     */
    private function assertComplete(ProductInterface $product, $localeCode, $requiredCount)
    {
        $this->assertCompletenessesCount($product, 2);

        $completeness = $this->getCompletenessByLocaleCode($product, $localeCode);

        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals($localeCode, $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('ecommerce', $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals($requiredCount, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
        $this->assertEquals(0, $completeness->getMissingAttributes()->count());
    }
}
