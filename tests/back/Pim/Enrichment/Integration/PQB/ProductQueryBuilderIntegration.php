<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;

/**
 * Tests that queries results executed by combining filters with different operators are consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilderIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * Combines several filters and operator to find the product
     */
    public function testComplexQuery1()
    {
        $foundProductQuery = $this->createPQBWithoutFamilyFilter();
        $foundProductQuery->addFilter('family', Operators::IN_LIST, ['familyA']);

        $productsFound = $foundProductQuery->execute();
        $this->assertCount(1, $productsFound);
        $this->assertEquals('complex_product_1', $productsFound->current()->getIdentifier());

        $notFoundProductQuery = $this->createPQBWithoutFamilyFilter();
        $notFoundProductQuery->addFilter('family', Operators::IS_EMPTY, null);

        $productsNotFound = $notFoundProductQuery->execute();
        $this->assertCount(0, $productsNotFound);
    }

    public function testComplexQuery2()
    {
        $foundProductQuery = $this->createPQBWithoutaLocalizedAndScopableTextAreaFilter();
        $foundProductQuery->addFilter(
            'a_localized_and_scopable_text_area',
            Operators::IS_EMPTY,
            'Mon textarea localisé et scopable ecommerce',
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        );

        $productsFound = $foundProductQuery->execute();
        $this->assertCount(1, $productsFound);
        $this->assertEquals('complex_product_1', $productsFound->current()->getIdentifier());

        $notFoundProductQuery = $this->createPQBWithoutaLocalizedAndScopableTextAreaFilter();
        $notFoundProductQuery->addFilter(
            'a_localized_and_scopable_text_area',
            Operators::STARTS_WITH,
            'My',
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        );

        $productsNotFound = $notFoundProductQuery->execute();
        $this->assertCount(0, $productsNotFound);
    }

    /**
     * Combines several filters to find the product (using EMPTY like operators)
     */
    public function testComplexQueryWithEmptyOperator()
    {
        $foundProductQuery = $this->createPQBWithoutACategoriesFilter();
        $foundProductQuery->addFilter('categories', Operators::UNCLASSIFIED, null);

        $productsFound = $foundProductQuery->execute();
        $this->assertCount(1, $productsFound);
        $this->assertEquals('complex_product_1', $productsFound->current()->getIdentifier());

        $notFoundProductQuery = $this->createPQBWithoutACategoriesFilter();
        $notFoundProductQuery->addFilter('categories', Operators::IN_CHILDREN_LIST, ['master']);

        $productsNotFound = $notFoundProductQuery->execute();
        $this->assertCount(0, $productsNotFound);
    }

    public function testAddAnAttributeFilterIsCaseInsensitive(): void
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('family', Operators::IN_LIST, ['familyA']);
        $pqb->addFilter('A_FILE', Operators::STARTS_WITH, 'aken');
        $pqb->addFilter('a_localizable_IMAGE', Operators::CONTAINS, 'akeneo', ['locale' => 'en_US']);
        $pqb->addFilter('a_regexp', Operators::CONTAINS, '1', ['locale' => 'en_US']);
        $pqb->addFilter(
            'a_SCOPABLE_price',
            Operators::GREATER_THAN,
            ['amount' => 13, 'currency' => 'USD'],
            ['scope' => 'ecommerce']
        );

        $productsFound = $pqb->execute();
        $this->assertCount(1, $productsFound);
    }

    public function testAddAnAttributeFilterIsCaseInsensitiveValues(): void
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('a_file', Operators::STARTS_WITH, 'AKEN');
        $productsFound = $pqb->execute();
        $this->assertCount(1, $productsFound);

        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('a_localizable_image', Operators::CONTAINS, 'AkenEO', ['locale' => 'en_US']);
        $productsFound = $pqb->execute();
        $this->assertCount(1, $productsFound);

        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('an_image', Operators::CONTAINS, 'AkenEO', ['locale' => 'en_US']);
        $productsFound = $pqb->execute();
        $this->assertCount(1, $productsFound);

        $this->createProduct('product_with_image', [
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('Akeneo Logo.jpg')))
        ]);
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('an_image', Operators::CONTAINS, 'aKENEO', ['locale' => 'en_US']);
        $productsFound = $pqb->execute();
        // should retrieve both akeneo.jpg and Akeneo Logo.jpg
        $this->assertCount(2, $productsFound);

        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('an_image', Operators::CONTAINS, 'aKENEO lOGO', ['locale' => 'en_US']);
        $productsFound = $pqb->execute();
        // should retrieve Akeneo Logo.jpg
        $this->assertCount(1, $productsFound);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * Product anatomy
     * - a field
     * - a non-scopable/non-localizable attribute
     * - a localizable attribute
     * - a locale specific attribute
     * - a scopable attribute
     * - a localizable/scopable attribute
     *
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->activateLocaleForChannel('fr_FR', 'ecommerce');

        $this->createProduct(
            'complex_product_1',
            [
                new SetFamily('familyA'),
                new SetFileValue(
                    'a_file',
                    null,
                    null,
                    $this->getFileInfoKey($this->getFixturePath('akeneo.txt'))
                ),
                new SetImageValue('a_localizable_image', null, 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
                new SetImageValue('a_localizable_image', null, 'fr_FR', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
                new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
                new SetTextValue('a_regexp', null, null, '10000'),
                new SetPriceCollectionValue('a_scopable_price', 'ecommerce', null, [
                    new PriceValue(12, 'EUR'),
                    new PriceValue(14, 'USD'),
                ]),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'fr_FR', 'Mon textarea localisé et scopable ecommerce'),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'Mon textarea localisé et scopable tablet'),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'en_US', 'My localizable and scopable textearea tablet'),
            ]
        );
    }

    protected function createPQBWithoutFamilyFilter(): ProductQueryBuilderInterface
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('a_file', Operators::STARTS_WITH, 'aken');
        $pqb->addFilter('a_localizable_image', Operators::CONTAINS, 'akeneo', ['locale' => 'en_US']);
        $pqb->addFilter('a_regexp', Operators::CONTAINS, '1', ['locale' => 'en_US']);
        $pqb->addFilter(
            'a_scopable_price',
            Operators::GREATER_THAN,
            ['amount' => 13, 'currency' => 'USD'],
            ['scope' => 'ecommerce']
        );
        $pqb->addFilter(
            'a_localized_and_scopable_text_area',
            Operators::EQUALS,
            'Mon textarea localisé et scopable ecommerce',
            ['locale' => 'fr_FR', 'scope' => 'ecommerce']
        );

        return $pqb;
    }

    protected function createPQBWithoutaLocalizedAndScopableTextAreaFilter(): ProductQueryBuilderInterface
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter('family', Operators::IN_LIST, ['familyA']);
        $pqb->addFilter('a_file', Operators::STARTS_WITH, 'aken');
        $pqb->addFilter('a_localizable_image', Operators::CONTAINS, 'akeneo', ['locale' => 'en_US']);
        $pqb->addFilter('a_regexp', Operators::CONTAINS, '1', ['locale' => 'en_US']);
        $pqb->addFilter(
            'a_scopable_price',
            Operators::GREATER_THAN,
            ['amount' => 13, 'currency' => 'USD'],
            ['scope' => 'ecommerce']
        );

        return $pqb;
    }

    protected function createPQBWithoutACategoriesFilter(): ProductQueryBuilderInterface
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();
        $pqb->addFilter(
            'a_scopable_price',
            Operators::IS_EMPTY,
            ['amount' => '', 'currency' => ''],
            ['scope' => 'tablet']
        );
        $pqb->addFilter(
            'a_localized_and_scopable_text_area',
            Operators::IS_NOT_EMPTY,
            null,
            ['locale' => 'fr_FR', 'scope' => 'ecommerce']
        );

        return $pqb;
    }
}
