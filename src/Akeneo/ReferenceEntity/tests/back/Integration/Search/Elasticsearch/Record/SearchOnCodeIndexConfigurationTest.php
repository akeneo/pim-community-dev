<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * Testing the search usecases for the record grid for information in the code of the record.
 *
 * @see       https://akeneo.atlassian.net/wiki/spaces/AKN/pages/572424236/Search+an+entity+record
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchOnCodeIndexConfigurationTest extends SearchIntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_matches_on_the_exact_same_code()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('designer', 'ecommerce', 'fr_FR', ['stark']);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_matches_with_case_insensitive_on_the_same_code()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('designer', 'ecommerce', 'fr_FR', ['STaRk']);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_matches_with_case_some_part_of_the_code()
    {
        $matchingIdentifiers = $this->searchIndexHelper->search('designer', 'ecommerce', 'fr_FR', ['taR']);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    private function loadFixtures()
    {
        $this->searchIndexHelper->resetIndex();

        $rightCode = [
            'identifier'                  => 'designer_stark',
            'reference_entity_identifier' => 'designer',
            'record_list_search'          => ['ecommerce' => ['fr_FR' => 'stark']] // Lets say the labels are empty
        ];
        $wrongCode = [
            'identifier'                  => 'designer_coco',
            'reference_entity_identifier' => 'designer',
            'record_list_search'          => ['ecommerce' => ['fr_FR' => 'coco']],
        ];
        $wrongEnrichedEntity = [
            'identifier'                  => 'manufacturer_stark',
            'reference_entity_identifier' => 'manufacturer',
            'record_list_search'          => ['ecommerce' => ['fr_FR' => 'stark']],
        ];
        $this->searchIndexHelper->index([$rightCode, $wrongCode, $wrongEnrichedEntity]);
    }
}
