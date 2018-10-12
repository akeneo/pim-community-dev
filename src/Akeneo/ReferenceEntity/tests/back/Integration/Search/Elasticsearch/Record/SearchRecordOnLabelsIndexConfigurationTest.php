<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * Testing the search usecases for the record grid for information in labels.
 *
 * @see       https://akeneo.atlassian.net/wiki/spaces/AKN/pages/572424236/Search+an+entity+record
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchRecordOnLabelsIndexConfigurationTest extends SearchIntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_matches_on_the_label_for_one_locale()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('brand', 'ecommerce', 'fr_FR', ['chaussure']);
        Assert::assertSame(['brand_shoes'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_matches_with_case_insensitive_on_labels_for_one_locale()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('brand', 'ecommerce', 'fr_FR', ['chaussure']);
        Assert::assertSame(['brand_shoes'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_matches_on_part_of_the_label()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('brand', 'ecommerce', 'fr_FR', ['aussu']);
        Assert::assertSame(['brand_shoes'], $matchingIdentifiers);
    }

    /**
     * @test
     *      Given shoes has 'tshirt' in the label fr_FR
     *      And teas has 'tshirt' in the label en_US
     *      When searching for 'tshirt' in en_US
     *      Then teas should be the result
     */
    public function it_matches_only_for_the_given_locale()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('brand', 'ecommerce', 'en_US', ['tshirt']);
        Assert::assertSame(['brand_teas'], $matchingIdentifiers);
    }

    /**
     * @test
     *      Given shoes has 'supérieur' in the label fr_FR
     *      When searching for 'superieur' in fr_FR
     *      Then tshirt should be the result
     */
    public function it_matches_on_special_characters()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('brand', 'ecommerce', 'fr_FR', ['supérieure']);
        Assert::assertSame(['brand_teas'], $matchingIdentifiers);
    }

    /**
     * @test
     *      For 'fr_FR'
     *      'marque' => matches shoe and tea
     *      'tshirt' => matches only tea
     *      Search returns tea
     */
    public function it_matche_on_multiple_words()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('brand', 'ecommerce', 'fr_FR', ['arqu', 'périeu']);
        Assert::assertSame(['brand_teas'], $matchingIdentifiers);
    }

    /**
     * @test
     *      For 'fr_FR'
     *      'marque' => matches shoe and tea
     *      'tshirt' => matches only tea
     *      Search returns tea
     */
    public function it_matche_multiple_documents_on_multiple_words()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('brand', 'ecommerce', 'en_US', ['the', 'best']);
        sort($matchingIdentifiers);
        Assert::assertSame(['brand_shoes', 'brand_teas'], $matchingIdentifiers);
    }

    /**
     * @todo it matches on superieur (without the 'é') ?
     */

    private function loadFixtures()
    {
        $shoe = [
            'reference_entity_identifier' => 'brand',
            'identifier'                  => 'brand_shoes',
            'record_list_search'          => [
                'ecommerce' => [
                    // Concatenated code and labels for locale and all text attributes for the locale
                    'en_US' => 'shoes The best shoe brand',
                    'fr_FR' => 'shoes La meilleure marque de chaussure et pas de tshirt',

                ],
            ],
        ];
        $tea = [
            'reference_entity_identifier' => 'brand',
            'identifier'                  => 'brand_teas',
            'record_list_search'          => [
                'ecommerce' => [
                    'en_US' => 'teas The best tshirt brand',
                    'fr_FR' => 'teas marque de tshirt supérieure',

                ],
            ],
        ];
        $wrongEnrichedEntity = [
            'identifier' => 'manufacturer_coco',
            'reference_entity_identifier' => 'manufacturer',
            'record_list_search' => [
                'ecommerce' => [
                    'fr_FR' => 'stark Designer supérieure',
                    'en_US' => 'stark the best designer',
                ],
            ],
        ];
        $this->searchIndexHelper->index([$shoe, $tea, $wrongEnrichedEntity]);
    }
}
